<?php

namespace App\Filament\Pages;

use App\Models\ConversacionAsistente;
use App\Models\MensajeAsistente;
use App\Services\Ajustes;
use App\Services\Asistente\Asistente;
use App\Services\Asistente\AsistenteConfig;
use App\Services\Asistente\BaseConocimiento;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/**
 * Configuración del asistente virtual: encendido, API key de Anthropic (cifrada), modelo, textos del chat, tono,
 * límites y retención. Incluye una prueba que pregunta al asistente con la configuración guardada.
 */
class ConfiguracionAsistente extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Asistente';

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del asistente';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.configuracion-asistente';

    public ?array $data = [];

    public ?string $prueba = null;

    public ?string $respuestaPrueba = null;

    public static function canAccess(): bool
    {
        // Maneja una credencial con costo: solo quien administra el sistema.
        return (bool) auth()->user()?->can('sistema.administrar');
    }

    public function mount(AsistenteConfig $config): void
    {
        $datos = $config->todo();
        $datos['sugerencias'] = collect($datos['sugerencias'])->map(fn ($texto) => ['texto' => $texto])->all();
        $this->form->fill($datos + ['api_key' => null]);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Encendido y conexión')->columns(2)->schema([
                Forms\Components\Toggle::make('activo')->label('Mostrar el asistente en el sitio')->columnSpanFull()
                    ->helperText('Aparece como un botón «Pregúntale al asistente» en todas las páginas. Necesita la API key.'),
                Forms\Components\TextInput::make('api_key')->label('API key de Anthropic')->password()->revealable()->maxLength(500)->autocomplete('new-password')->columnSpanFull()
                    ->placeholder(fn () => app(AsistenteConfig::class)->tieneApiKey() ? 'Guardada. Escribe una nueva solo para cambiarla.' : 'sk-ant-…')
                    ->helperText('Se crea en console.anthropic.com → API Keys. Se guarda cifrada y no queda en la bitácora. Cada pregunta tiene un costo que se cobra a esa cuenta.'),
                Forms\Components\Select::make('modelo')->label('Modelo')->options(AsistenteConfig::MODELOS)->required()->native(false),
                Forms\Components\Select::make('esfuerzo')->label('Esfuerzo de razonamiento')->options(AsistenteConfig::ESFUERZOS)->required()->native(false),
                Forms\Components\Toggle::make('incluir_ejes')->label('Usar también los ejes de propuestas publicados')->columnSpanFull()
                    ->helperText('Además de la base de conocimiento, el asistente lee los ejes publicados en «Ejes y propuestas» (título, frase, compromisos y textos).'),
            ]),
            Forms\Components\Section::make('Lo que ve la persona')->columns(2)->schema([
                Forms\Components\TextInput::make('nombre')->label('Nombre del asistente')->required()->maxLength(60),
                Forms\Components\Textarea::make('bienvenida')->label('Mensaje de bienvenida')->required()->maxLength(300)->rows(3)->columnSpanFull(),
                Forms\Components\Repeater::make('sugerencias')->label('Preguntas sugeridas')->simple(Forms\Components\TextInput::make('texto')->required()->maxLength(80))
                    ->maxItems(4)->defaultItems(0)->reorderable()->addActionLabel('Agregar pregunta')->columnSpanFull()
                    ->helperText('Botones que aparecen antes de la primera pregunta. Máximo 4.'),
            ]),
            Forms\Components\Section::make('Tono')->schema([
                Forms\Components\Textarea::make('instrucciones')->label('Indicaciones del equipo de comunicaciones')->maxLength(2000)->rows(5)
                    ->placeholder('Ejemplo: Habla con calidez y cercanía, como alguien del barrio. Cuando hablen de seguridad, recuerda mencionar el eje de convivencia.')
                    ->helperText('Cómo debe sonar. No pongas aquí datos de Rosa: los datos van en la base de conocimiento, con su fuente. Las reglas fijas (no inventar, no pedir el voto en precampaña, no hablar de encuestas, no pedir datos personales) no se pueden cambiar desde aquí.'),
            ]),
            Forms\Components\Section::make('Límites y privacidad')->columns(3)->schema([
                Forms\Components\TextInput::make('max_por_conversacion')->label('Preguntas por conversación')->numeric()->integer()->minValue(5)->maxValue(100)->required(),
                Forms\Components\TextInput::make('max_diario')->label('Preguntas por día (todo el sitio)')->numeric()->integer()->minValue(10)->maxValue(100000)->required()
                    ->helperText('Tope de gasto: al llegar, el asistente descansa hasta el día siguiente.'),
                Forms\Components\TextInput::make('dias_retencion')->label('Días que se guardan las conversaciones')->numeric()->integer()->minValue(1)->maxValue(365)->required()
                    ->helperText('Después se borran solas. Debe coincidir con la política de datos.'),
            ]),
        ]);
    }

    public function guardar(Ajustes $ajustes, AsistenteConfig $config): void
    {
        $datos = $this->form->getState();

        if (filled($datos['api_key'] ?? null)) {
            $config->guardarApiKey($datos['api_key']);
        }
        unset($datos['api_key']);

        $datos['sugerencias'] = array_values(array_filter(array_map(
            fn ($fila) => is_array($fila) ? ($fila['texto'] ?? null) : $fila,
            (array) ($datos['sugerencias'] ?? []),
        ), 'filled'));
        foreach (['max_por_conversacion', 'max_diario', 'dias_retencion'] as $numero) {
            $datos[$numero] = (int) $datos[$numero];
        }

        $ajustes->set(AsistenteConfig::CLAVE, $datos, 'asistente');
        $this->data['api_key'] = null;

        if ($datos['activo'] && ! $config->tieneApiKey()) {
            Notification::make()->warning()->title('Guardado, pero el asistente no aparece todavía')->body('Falta la API key de Anthropic.')->send();

            return;
        }

        Notification::make()->success()->title('Configuración del asistente guardada')
            ->body($config->disponible() ? 'El asistente está visible en el sitio.' : 'El asistente está apagado en el sitio.')->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('probar')->label('Probar el asistente')->icon('heroicon-o-chat-bubble-left-right')
                ->disabled(fn () => ! app(AsistenteConfig::class)->tieneApiKey())
                ->form([Forms\Components\Textarea::make('pregunta')->label('Pregunta de prueba')->required()->maxLength(Asistente::MAX_CARACTERES)->default('¿Quién es Rosa Acevedo?')])
                ->modalDescription('Usa la configuración y la base de conocimiento guardadas. La prueba queda en Conversaciones y cuenta en el límite diario.')
                ->action(function (array $data, Asistente $asistente) {
                    $conversacion = ConversacionAsistente::query()->create(['uuid' => (string) Str::uuid(), 'pagina' => '/admin (prueba)']);
                    $respuesta = $asistente->responder($conversacion, $data['pregunta']);
                    $this->prueba = $data['pregunta'];
                    $this->respuestaPrueba = $respuesta['texto'];

                    Notification::make()->title($respuesta['estado'] === 'ok' ? 'El asistente respondió' : 'El asistente no pudo responder')
                        ->{$respuesta['estado'] === 'ok' ? 'success' : 'warning'}()->send();
                }),
        ];
    }

    /** Datos para la vista: estado, tamaño de la base y uso. */
    public function resumen(): array
    {
        $config = app(AsistenteConfig::class);
        $base = app(BaseConocimiento::class);
        $hoy = now()->startOfDay();

        return [
            'disponible' => $config->disponible(),
            'api_key' => $config->tieneApiKey(),
            'tokens' => $base->tokensAproximados(),
            'aviso' => mb_strlen($base->texto()) > BaseConocimiento::AVISO_CARACTERES,
            'preguntas_hoy' => MensajeAsistente::query()->where('rol', 'usuario')->where('created_at', '>=', $hoy)->count(),
            'conversaciones_30' => ConversacionAsistente::query()->where('created_at', '>=', now()->subDays(30))->count(),
            'tokens_30' => (int) ConversacionAsistente::query()->where('created_at', '>=', now()->subDays(30))->sum('tokens_entrada'),
            'salida_30' => (int) ConversacionAsistente::query()->where('created_at', '>=', now()->subDays(30))->sum('tokens_salida'),
            'max_diario' => (int) $config->get('max_diario'),
        ];
    }
}
