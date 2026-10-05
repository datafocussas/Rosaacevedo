<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Roles y permisos de la sección 03. El Administrador puede todo (Gate::before). */
class RolesSeeder extends Seeder
{
    public const PERMISOS = [
        'contenido.gestionar' => 'Banners, noticias, páginas, ejes, agenda, medios, comunas y enlaces',
        'sitio.configurar' => 'Configuración, menú, redes y temas',
        'sitio.modo' => 'Cambiar el modo del sitio',
        'propuestas.ver' => 'Ver propuestas ciudadanas sin datos personales',
        'propuestas.moderar' => 'Moderar y responder propuestas',
        'registros.ver' => 'Ver registros con datos personales',
        'registros.ver_limitado' => 'Ver registros: solo nombre y comuna',
        'registros.exportar' => 'Exportar registros (con motivo)',
        'titular.gestionar' => 'Atender solicitudes del titular',
        'enlaces.qr' => 'Crear enlaces cortos y QR',
        'ab.ver' => 'Ver el reporte de pruebas A/B',
        'sistema.administrar' => 'Políticas, usuarios, roles, bitácora y redirecciones',
    ];

    public const ROLES = [
        'administrador' => ['*'],
        'editor' => ['contenido.gestionar', 'sitio.configurar', 'propuestas.ver', 'enlaces.qr', 'ab.ver'],
        'moderador' => ['propuestas.ver', 'propuestas.moderar', 'registros.ver_limitado', 'titular.gestionar'],
        'analista' => ['propuestas.ver', 'registros.ver', 'registros.exportar', 'ab.ver'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::PERMISOS) as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        foreach (self::ROLES as $rol => $permisos) {
            Role::findOrCreate($rol, 'web')->syncPermissions($permisos === ['*'] ? array_keys(self::PERMISOS) : $permisos);
        }
    }
}
