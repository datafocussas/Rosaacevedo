<?php

namespace App\Filament\Pages;

use App\Jobs\SincronizarCrm;
use App\Models\Ciudadano;
use App\Models\CrmOutbox;
use App\Services\Ajustes;
use App\Services\Crm\CrmConexion;
use App\Services\Crm\Payloads;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Conexión con el CRM de la campaña (RF-07): URL por tipo de dato, API key cifrada, campos a enviar y
 * estado de la sincronización. El envío es automático: cada minuto el programador toma lo pendiente del
 * outbox y lo entrega; si el CRM falla, reintenta con espera creciente sin perder datos.
 */
class ConexionCrm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $title = 'Conexión con el CRM';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.conexion-crm';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        // Maneja una credencial y datos personales: solo quien administra el sistema.
        return (bool) auth()->user()?->can('sistema.administrar');
    }

    public function mount(CrmConexion $conexion): void
    {
        $this->form->fill($conexion->config() + ['api_key' => null]);
    }

    public function form(Form $form): Form
    {
        $opciones = fn (string $entidad) => CrmConexion::CAMPOS[$entidad];

        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Conexión')->columns(2)->schema([
                Forms\Components\Toggle::make('activo')->label('Enviar datos al CRM')->columnSpanFull()
                    ->helperText('Mientras esté apagado, el sitio sigue captando y guarda todo en espera. Al encenderlo se envía también lo que quedó pendiente.'),
                Forms\Components\TextInput::make('url_ciudadano')->label('URL para registros (leads)')->url()->maxLength(255)->columnSpanFull()
                    ->placeholder('https://aplicativo.rosaacevedo.co/api/…')
                    ->helperText('Dirección completa del endpoint del CRM que recibe un registro (POST con JSON). Tómala de la documentación de la API del CRM.')
                    ->requiredIf('activo', true),
                Forms\Components\TextInput::make('url_propuesta')->label('URL para propuestas del buzón (opcional)')->url()->maxLength(255)->columnSpanFull()
                    ->helperText('Si queda vacía, las propuestas esperan en el sitio y no se envían.'),
                Forms\Components\Select::make('autenticacion')->label('Autenticación')->options(CrmConexion::AUTENTICACION)->required()->native(false)->live(),
                Forms\Components\TextInput::make('encabezado')->label('Nombre del encabezado')->maxLength(60)->placeholder('X-API-Key')
                    ->visible(fn (Forms\Get $get) => $get('autenticacion') === 'header'),
                Forms\Components\TextInput::make('api_key')->label('API key')->password()->revealable()->maxLength(500)->autocomplete('new-password')
                    ->visible(fn (Forms\Get $get) => $get('autenticacion') !== 'ninguna')
                    ->placeholder(fn () => app(CrmConexion::class)->tieneApiKey() ? 'Guardada. Escribe una nueva solo para cambiarla.' : 'Pega aquí la API key del CRM')
                    ->helperText('Se guarda cifrada. No se muestra de nuevo ni queda en la bitácora.'),
            ]),
            Forms\Components\Section::make('Campos de los registros (leads)')
                ->description('Elige qué dato del sitio se envía y con qué nombre lo espera el CRM. Si no agregas ninguno, se envía el registro completo con los nombres del sitio. Para anidar, usa puntos: «contacto.nombre».')
                ->schema([
                    Forms\Components\Repeater::make('campos_ciudadano')->label('')->columns(2)->reorderable()->addActionLabel('Agregar campo')->defaultItems(0)
                        ->schema([
                            Forms\Components\Select::make('sitio')->label('Dato del sitio')->options($opciones('ciudadano'))->required()->native(false)->searchable(),
                            Forms\Components\TextInput::make('crm')->label('Nombre del campo en el CRM')->required()->maxLength(80),
                        ]),
                ]),
            Forms\Components\Section::make('Campos de las propuestas')->collapsed()
                ->description('Igual que los registros. Solo aplica si configuraste la URL para propuestas.')
                ->schema([
                    Forms\Components\Repeater::make('campos_propuesta')->label('')->columns(2)->reorderable()->addActionLabel('Agregar campo')->defaultItems(0)
                        ->schema([
                            Forms\Components\Select::make('sitio')->label('Dato del sitio')->options($opciones('propuesta'))->required()->native(false)->searchable(),
                            Forms\Components\TextInput::make('crm')->label('Nombre del campo en el CRM')->required()->maxLength(80),
                        ]),
                ]),
        ]);
    }

    public function guardar(Ajustes $ajustes, CrmConexion $conexion): void
    {
        $datos = $this->form->getState();

        if (filled($datos['api_key'] ?? null)) {
            $conexion->guardarApiKey($datos['api_key']);
        }
        unset($datos['api_key']);

        $ajustes->set(CrmConexion::CLAVE, $datos, 'crm');
        $this->data['api_key'] = null;

        if ($datos['activo'] && ! $conexion->activa()) {
            Notification::make()->warning()->title('Guardado, pero la conexión aún no envía')
                ->body('Falta la URL de registros o la API key.')->send();

            return;
        }

        Notification::make()->success()->title('Conexión con el CRM guardada')
            ->body($conexion->activa() ? 'El envío es automático cada minuto.' : 'El envío está apagado: los datos quedan en espera.')->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sincronizar')->label('Enviar pendientes ahora')->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalDescription('Envía ya lo pendiente, sin esperar al próximo minuto. Usa la configuración guardada.')
                ->disabled(fn () => ! CrmConexion::habilitada())
                ->action(function () {
                    $antes = CrmOutbox::query()->where('estado', 'enviado')->count();
                    dispatch_sync(new SincronizarCrm);
                    $enviados = CrmOutbox::query()->where('estado', 'enviado')->count() - $antes;
                    $error = CrmOutbox::query()->where('estado', 'error')->latest('updated_at')->value('ultimo_error');

                    Notification::make()->title("Enviados: {$enviados}")
                        ->body($error ? 'Último error: '.$error : 'Sin errores.')
                        ->{$error ? 'warning' : 'success'}()->send();
                }),
        ];
    }

    /** Resumen del outbox para la vista. */
    public function resumen(): array
    {
        $conteo = fn (string $entidad) => CrmOutbox::query()->where('entidad', $entidad)
            ->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado')->all();

        return [
            'activa' => app(CrmConexion::class)->activa(),
            'env' => config('rosa.crm.driver') === 'http',
            'ciudadano' => $conteo('ciudadano'),
            'propuesta' => $conteo('propuesta'),
            'leads' => Ciudadano::query()->selectRaw('crm_sync_estado, count(*) as total')->groupBy('crm_sync_estado')->pluck('total', 'crm_sync_estado')->all(),
            'errores' => CrmOutbox::query()->where('estado', 'error')->latest('updated_at')->limit(5)->get(['entidad', 'entidad_id', 'intentos', 'ultimo_error', 'proximo_intento', 'updated_at']),
        ];
    }

    /** Ejemplo del cuerpo que se enviaría con la configuración guardada, con el último registro (celular oculto). */
    public function ejemplo(): ?string
    {
        $ciudadano = Ciudadano::query()->latest('id')->first();
        if (! $ciudadano) {
            return null;
        }

        $payload = Payloads::ciudadano($ciudadano, 'actualizado');
        $payload['celular'] = $ciudadano->celularEnmascarado();

        return json_encode(app(CrmConexion::class)->cuerpo('ciudadano', $payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
