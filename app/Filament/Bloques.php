<?php

namespace App\Filament;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * Bloques reordenables de las páginas (RF-12): texto, imagen, imagen + texto, cita, línea de tiempo,
 * video, galería, cifras, llamado a la acción, formulario, lista de ejes y selector de comunas.
 * La página de inicio suma sus secciones propias, que se activan, desactivan y reordenan.
 */
class Bloques
{
    public static function builder(string $campo = 'bloques', bool $conInicio = false): Builder
    {
        $bloques = [...($conInicio ? self::inicio() : []), ...self::generales()];

        return Builder::make($campo)
            ->label('Bloques')
            ->blocks($bloques)
            ->reorderableWithButtons()
            ->collapsible()
            ->cloneable()
            ->blockNumbers(false)
            ->addActionLabel('Agregar bloque');
    }

    private static function activo(): Toggle
    {
        return Toggle::make('activo')->label('Visible')->default(true);
    }

    private static function imagen(string $nombre = 'imagen'): FileUpload
    {
        return FileUpload::make($nombre)->label('Imagen')->image()->disk('public')->directory('paginas')
            ->maxSize(4096)->imageEditor()->helperText('Máximo 200 KB recomendado (WebP o JPG optimizado).');
    }

    private static function alt(): TextInput
    {
        return TextInput::make('alt')->label('Texto alternativo')->maxLength(200)->required()
            ->helperText('Describe la imagen para quien no la ve. Obligatorio.');
    }

    public static function generales(): array
    {
        return [
            Block::make('texto')->label('Texto')->icon('heroicon-o-document-text')->schema([
                self::activo(),
                TextInput::make('titulo')->label('Título (opcional)')->maxLength(120),
                RichEditor::make('contenido')->label('Contenido')->required()
                    ->toolbarButtons(['bold', 'italic', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'redo', 'undo']),
            ]),
            Block::make('imagen')->label('Imagen')->icon('heroicon-o-photo')->schema([
                self::activo(), self::imagen()->required(), self::alt(),
                TextInput::make('pie')->label('Pie de foto')->maxLength(200),
            ]),
            Block::make('imagen_texto')->label('Imagen + texto')->icon('heroicon-o-rectangle-group')->schema([
                self::activo(), self::imagen(), TextInput::make('alt')->label('Texto alternativo')->maxLength(200)->requiredWith('imagen'),
                TextInput::make('etiqueta')->maxLength(60), TextInput::make('titulo')->label('Título')->maxLength(120),
                RichEditor::make('texto')->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),
                Toggle::make('invertir')->label('Imagen a la derecha'),
                Select::make('fondo')->options(['' => 'Crema', 'raiz' => 'Verde «Raíces»'])->native(false),
            ]),
            Block::make('cita')->label('Cita')->icon('heroicon-o-chat-bubble-bottom-center-text')->schema([
                self::activo(), Textarea::make('texto')->required()->rows(3), TextInput::make('autor')->maxLength(120),
            ]),
            Block::make('linea_tiempo')->label('Línea de tiempo')->icon('heroicon-o-clock')->schema([
                self::activo(), TextInput::make('etiqueta')->default('Raíces'), TextInput::make('titulo')->label('Título')->default('Trayectoria'),
                Repeater::make('items')->label('Hitos')->schema([
                    TextInput::make('periodo')->required()->maxLength(40),
                    TextInput::make('titulo')->label('Cargo o hito')->required()->maxLength(120),
                    Textarea::make('texto')->rows(2),
                ])->reorderableWithButtons()->collapsible()
                    ->helperText('Solo cargos y periodos confirmados con la hoja de vida oficial. Si falta, escribe [POR CONFIRMAR].'),
            ]),
            Block::make('video')->label('Video')->icon('heroicon-o-play')->schema([
                self::activo(), TextInput::make('url')->label('Enlace de YouTube o Vimeo')->url()->required(), TextInput::make('titulo')->label('Título')->maxLength(120)->required(),
            ]),
            Block::make('galeria')->label('Galería')->icon('heroicon-o-squares-2x2')->schema([
                self::activo(), TextInput::make('titulo')->label('Título')->maxLength(120),
                self::imagen('imagenes')->multiple()->reorderable()->required(), self::alt(),
            ]),
            Block::make('cifras')->label('Cifras')->icon('heroicon-o-chart-bar')->schema([
                self::activo(),
                Repeater::make('items')->label('Cifras')->schema([
                    TextInput::make('valor')->required()->maxLength(20), TextInput::make('texto')->required()->maxLength(80),
                ])->maxItems(4)->grid(2)
                    ->helperText('Solo cifras confirmadas con su fuente. Nunca cifras de la Encuesta Itagüí 2026.'),
            ]),
            Block::make('llamado')->label('Llamado a la acción')->icon('heroicon-o-megaphone')->schema([
                self::activo(), TextInput::make('etiqueta')->maxLength(60), TextInput::make('titulo')->label('Título')->required()->maxLength(120),
                TextInput::make('texto')->maxLength(200), TextInput::make('boton_texto')->label('Texto del botón')->maxLength(30)->default('Súmate'),
                TextInput::make('boton_url')->label('Enlace del botón')->default('/sumate'),
            ]),
            Block::make('formulario')->label('Formulario')->icon('heroicon-o-pencil-square')->schema([
                self::activo(), Select::make('tipo')->options(['registro' => 'Registro (Súmate)', 'buzon' => 'Buzón ciudadano'])->default('registro')->required()->native(false),
                TextInput::make('titulo')->label('Título')->maxLength(80),
            ]),
            Block::make('ejes')->label('Lista de ejes')->icon('heroicon-o-list-bullet')->schema([
                self::activo(), TextInput::make('etiqueta')->default('Propuestas'), TextInput::make('titulo')->label('Título')->default('Aquí me planto por…'), Textarea::make('texto')->rows(2), TextInput::make('boton_texto')->default('Conoce las propuestas'),
            ]),
            Block::make('comunas')->label('Selector de comunas')->icon('heroicon-o-map')->schema([
                self::activo(), TextInput::make('etiqueta')->default('Tu comuna'), TextInput::make('titulo')->label('Título')->default('Aquí me planto en cada barrio'),
            ]),
        ];
    }

    /** Secciones propias de la página de inicio. */
    public static function inicio(): array
    {
        return [
            Block::make('registro')->label('Inicio · Entrada con registro')->icon('heroicon-o-user-plus')->schema([
                self::activo(),
                TextInput::make('etiqueta_formulario')->label('Rótulo del formulario')->default('Súmate a la siembra'),
                TextInput::make('titulo_formulario')->label('Título del formulario')->default('Recibe las propuestas para tu barrio'),
            ])->maxItems(1),
            Block::make('raices')->label('Inicio · Franja «Raíces»')->icon('heroicon-o-sparkles')->schema([
                self::activo(), self::imagen(), TextInput::make('alt')->label('Texto alternativo')->default('Rosa Acevedo')->maxLength(200),
                TextInput::make('etiqueta')->default('Raíces'), TextInput::make('titulo')->label('Título')->default('Rosa no se trasplanta'),
                Textarea::make('texto')->rows(3)->helperText('Solo datos biográficos confirmados. Si falta, escribe [POR CONFIRMAR].'),
                TextInput::make('enlace_texto')->default('Lee el manifiesto'), TextInput::make('enlace_url')->default('/manifiesto'),
            ]),
            Block::make('buzon')->label('Inicio · Llamado al buzón')->icon('heroicon-o-inbox')->schema([
                self::activo(), TextInput::make('etiqueta')->default('Buzón ciudadano'), TextInput::make('titulo')->label('Título')->default('¿Qué necesita tu barrio?'),
                TextInput::make('texto'), TextInput::make('boton_texto')->default('Deja tu propuesta'),
            ]),
            Block::make('noticias')->label('Inicio · Últimas noticias')->icon('heroicon-o-newspaper')->schema([self::activo(), TextInput::make('titulo')->label('Título')->default('Noticias')]),
            Block::make('agenda')->label('Inicio · Próximos encuentros')->icon('heroicon-o-calendar')->schema([self::activo(), TextInput::make('etiqueta')->default('Agenda'), TextInput::make('titulo')->label('Título')->default('Nos vemos en el barrio')]),
            Block::make('redes')->label('Inicio · Franja de redes')->icon('heroicon-o-share')->schema([self::activo()]),
        ];
    }
}
