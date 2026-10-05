<?php

namespace Database\Seeders;

use App\Models\Politica;
use Illuminate\Database\Seeder;

/**
 * Versión 0.1 (borrador técnico) de cada texto legal, para que los formularios funcionen en local y en
 * pruebas. Antes de salir a producción el asesor jurídico aprueba la versión 1.0 y se publica desde el
 * panel (Sitio → Políticas). Los textos de las casillas siguen la vista de referencia Consentimiento.
 */
class PoliticasSeeder extends Seeder
{
    public function run(): void
    {
        $textos = [
            'autorizacion_general' => 'Autorizo a la campaña Rosa Acevedo Alcaldía 2027 a tratar mis datos para informarme y contactarme, según la política de tratamiento de datos.',
            'whatsapp' => 'Acepto recibir mensajes de la campaña por WhatsApp.',
            'afinidad_politica' => 'Acepto compartir mi afinidad política para recibir información de la campaña.',
            'publicar_propuesta' => 'Pueden publicar mi propuesta sin mi nombre.',
            'tratamiento_datos' => self::politicaBorrador(),
            'cookies' => 'Por defecto usamos solo cookies técnicas: sesión, protección contra falsificación de formularios y la variante de la página que ves (prueba A/B de primera parte). La analítica del sitio (Umami) no usa cookies. Las publicaciones incrustadas de redes sociales y la medición de campañas solo se activan si las aceptas. Tu elección se guarda seis meses.',
            'uso_ia' => 'Usamos herramientas de inteligencia artificial para sugerir el tema, la comuna y un resumen de las propuestas que recibimos. Una persona del equipo revisa y confirma cada clasificación. La herramienta recibe solo el texto de la propuesta, sin tu nombre ni tu celular.',
        ];

        foreach ($textos as $tipo => $texto) {
            if (Politica::query()->where('tipo', $tipo)->exists()) {
                continue;
            }

            $politica = Politica::query()->create([
                'tipo' => $tipo,
                'version' => '0.1',
                'texto' => $texto,
                'vigente_desde' => now(),
                'vigente' => false,
            ]);
            $politica->activar();
        }
    }

    private static function politicaBorrador(): string
    {
        return <<<'MD'
**[POR CONFIRMAR] Borrador técnico pendiente de aprobación jurídica. No publicar en producción con esta versión.**

## Responsable del tratamiento

[POR CONFIRMAR] Nombre o razón social, NIT o cédula, dirección y correo del responsable del tratamiento.

## Qué datos recogemos

- En el registro: nombre, celular y, si quieres, correo y barrio o vereda.
- En el voluntariado: cómo quieres ayudar, tu disponibilidad y, solo si lo compartes, tu puesto de votación.
- En el buzón ciudadano: tu propuesta, el tema, el barrio y, si la adjuntas, una foto.
- No pedimos tu cédula.

## Para qué los usamos

- Informarte sobre la campaña y las propuestas para tu comuna.
- Invitarte a encuentros en tu barrio.
- Organizar el voluntariado.
- Leer, clasificar y responder tus propuestas.

## Segmentación por comuna e intereses

Usamos tu barrio para ubicarte en tu comuna (división del Acuerdo 017 de 2024) y en la división anterior, que rige la elección de las JAL. Con tus intereses te mostramos información relacionada. No te perfilamos por afinidad política si no lo autorizas expresamente.

## Datos sensibles

La afinidad política es un dato sensible. No estás obligado a darla. Solo la tratamos si marcas la casilla correspondiente.

## WhatsApp

Solo te escribimos por WhatsApp si lo autorizas. Nunca te agregamos a grupos o listas sin tu consentimiento previo.

## Inteligencia artificial

Usamos inteligencia artificial para sugerir la clasificación de las propuestas. Una persona revisa cada sugerencia. Más información en la página de uso de inteligencia artificial.

## Con quién compartimos tus datos

Con el sistema de gestión de la campaña (CRM), que es la fuente de verdad de tu registro. [POR CONFIRMAR] Encargados del tratamiento (proveedores de alojamiento y correo).

## Seguridad

Tu celular se guarda cifrado. El acceso al panel exige doble factor y cada consulta queda registrada.

## Tus derechos

Puedes conocer, actualizar, rectificar y suprimir tus datos, y revocar tu autorización, en la página Mis datos. Respondemos consultas en máximo 10 días hábiles y reclamos en máximo 15 días hábiles (Ley 1581 de 2012).

## Vigencia

[POR CONFIRMAR] Fecha de entrada en vigencia y periodo de conservación de los datos.
MD;
    }
}
