<?php

namespace App\Filament\Pages;

use App\Services\Ajustes;
use App\Services\Crm\CrmConexion;
use App\Support\CacheSitio;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Configuración global (RF-41). Cada cambio queda en la bitácora. El modo del sitio solo lo cambia el
 * Administrador. Las llaves secretas (Turnstile, Umami, CRM) viven en el .env, no en la base de datos.
 */
class Configuracion extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $title = 'Configuración';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.configuracion';

    public ?array $data = [];

    private const CLAVES = ['modo_sitio', 'aviso_global', 'whatsapp_numero', 'whatsapp_mensaje', 'whatsapp_canal', 'hashtag', 'contacto_email',
        'responsable_tratamiento', 'pie_frase', 'pie_leyenda_financiacion', 'transparencia', 'imagen_redes'];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('sitio.configurar');
    }

    public function mount(Ajustes $ajustes): void
    {
        $ajustes->olvidar();
        $this->form->fill(collect(self::CLAVES)->mapWithKeys(fn ($c) => [$c => $ajustes->get($c)])->all());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Modo del sitio')->schema([
                Forms\Components\Radio::make('modo_sitio')->label('')->options(Ajustes::MODOS)->required()
                    ->descriptions([
                        'precampana' => 'Llamados de participación. No se pide el voto.',
                        'campana' => 'Habilita «Vota», la leyenda de financiación y la página de Transparencia. Solo dentro de los plazos de la Ley 1475.',
                        'dia_d' => 'Jornada electoral.',
                    ])
                    ->disabled(fn () => ! auth()->user()->can('sitio.modo'))
                    ->helperText(fn () => auth()->user()->can('sitio.modo') ? null : 'Solo el Administrador cambia el modo.'),
            ]),
            Forms\Components\Section::make('Aviso de escucha ciudadana')->description('Franja verde bajo la entrada de inicio y de las páginas de comuna.')->columns(3)->schema([
                Forms\Components\Toggle::make('aviso_global.activo')->label('Visible'),
                Forms\Components\TextInput::make('aviso_global.texto')->label('Texto')->maxLength(120),
                Forms\Components\TextInput::make('aviso_global.url')->label('Enlace del botón')->maxLength(255),
                Forms\Components\TextInput::make('aviso_global.boton')->label('Texto del botón')->maxLength(30)->placeholder('Deja tu propuesta'),
            ]),
            Forms\Components\Section::make('WhatsApp y redes')->columns(2)->schema([
                Forms\Components\TextInput::make('whatsapp_numero')->label('Número de WhatsApp Business')->placeholder('573001234567')
                    ->helperText('Con indicativo 57, sin espacios. Distinto del canal.')->regex('/^(57\d{10}|57X+)$/'),
                Forms\Components\TextInput::make('whatsapp_mensaje')->label('Mensaje prellenado')->maxLength(200)
                    ->helperText('{codigo} se reemplaza por el código de origen.'),
                Forms\Components\TextInput::make('whatsapp_canal')->label('Canal de difusión')->url(),
                Forms\Components\TextInput::make('hashtag')->label('Hashtag vigente')->maxLength(60),
            ]),
            Forms\Components\Section::make('Datos legales y contacto')->columns(3)->schema([
                Forms\Components\TextInput::make('responsable_tratamiento.nombre')->label('Responsable del tratamiento')->required(),
                Forms\Components\TextInput::make('responsable_tratamiento.identificacion')->label('NIT o cédula')->required(),
                Forms\Components\TextInput::make('responsable_tratamiento.email')->label('Correo para datos personales')->email()->required(),
                Forms\Components\TextInput::make('contacto_email')->label('Correo de contacto')->email(),
                Forms\Components\TextInput::make('pie_frase')->label('Frase del pie')->maxLength(160)->columnSpan(2),
                Forms\Components\Textarea::make('pie_leyenda_financiacion')->label('Leyenda de financiación (modo campaña)')->rows(2)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Transparencia (modo campaña)')->columns(2)->collapsed()->schema([
                Forms\Components\TextInput::make('transparencia.gerente')->label('Gerente de campaña'),
                Forms\Components\TextInput::make('transparencia.cuentas_claras')->label('Enlace a Cuentas Claras')->url(),
                Forms\Components\RichEditor::make('transparencia.texto')->label('Texto')->columnSpanFull()->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),
            ]),
            Forms\Components\Section::make('Redes sociales: imagen por defecto')->schema([
                Forms\Components\FileUpload::make('imagen_redes')->label('Imagen para compartir (1200 × 630)')->image()->disk('public')->directory('sitio')
                    ->imageEditor()->imageEditorAspectRatios(['1.91:1'])->maxSize(2048),
            ]),
            Forms\Components\Section::make('Integraciones')->description('La conexión con el CRM se configura en Sitio → Conexión con el CRM. Turnstile, Umami y el correo, en el .env del servidor.')->collapsed()->schema([
                Forms\Components\Placeholder::make('estado_integraciones')->label('')->content(fn () => collect([
                    'Turnstile' => filled(config('rosa.turnstile.secret')),
                    'Umami' => filled(config('rosa.umami.website_id')),
                    'CRM' => CrmConexion::habilitada(),
                    'Correo (Brevo)' => config('mail.default') === 'smtp',
                ])->map(fn ($ok, $nombre) => $nombre.': '.($ok ? 'activo' : 'sin configurar'))->join(' · ')),
            ]),
        ]);
    }

    public function guardar(Ajustes $ajustes): void
    {
        $datos = $this->form->getState();

        if (! auth()->user()->can('sitio.modo')) {
            unset($datos['modo_sitio']);
        }

        foreach ($datos as $clave => $valor) {
            if (in_array($clave, self::CLAVES, true)) {
                $ajustes->set($clave, $valor);
            }
        }

        CacheSitio::limpiar();
        Notification::make()->title('Configuración guardada')->success()->send();
    }
}
