<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use Illuminate\Database\Seeder;

class AjustesSeeder extends Seeder
{
    public function run(): void
    {
        $ajustes = [
            // Del script de referencia (schema-mysql.sql).
            ['modo_sitio', 'precampana', 'sitio'],
            ['aviso_global', ['activo' => true, 'texto' => 'Escucha ciudadana abierta: deja tu propuesta para tu barrio', 'url' => '/buzon'], 'sitio'],
            ['whatsapp_numero', '57XXXXXXXXXX', 'redes'],
            ['whatsapp_canal', 'https://whatsapp.com/channel/0029Vb6auV20QeanaeczZw2g', 'redes'],
            ['whatsapp_mensaje', 'Hola, quiero saber más de Rosa. Código: {codigo}', 'redes'],
            ['responsable_tratamiento', ['nombre' => '[por definir]', 'identificacion' => '[NIT o cédula]', 'email' => 'datos@rosaacevedo.com'], 'legal'],
            // Propios del sitio.
            ['hashtag', '#AquíMePlantoPorItagüí', 'redes'],
            ['contacto_email', 'contacto@rosaacevedo.com', 'sitio'],
            ['pie_frase', 'Mis raíces están aquí. Y las raíces no se trasplantan.', 'sitio'],
            ['pie_leyenda_financiacion', null, 'legal'],
            ['transparencia', ['gerente' => null, 'cuentas_claras' => 'https://www.cnecuentasclaras.gov.co/', 'texto' => null], 'legal'],
            ['imagen_redes', null, 'sitio'],
        ];

        foreach ($ajustes as [$clave, $valor, $grupo]) {
            Ajuste::query()->firstOrCreate(['clave' => $clave], ['valor' => $valor, 'grupo' => $grupo]);
        }
    }
}
