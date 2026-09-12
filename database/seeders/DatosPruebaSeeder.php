<?php

namespace Database\Seeders;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\RolModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use App\Contexto\Menu\Infraestructura\Eloquent\Models\MenuModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Crea datos de prueba: empresas, usuarios, roles, menus y asignaciones.
     */
    public function run(): void
    {
        // === Empresas ===
        $empresa1 = EmpresaModel::create([
            'nombre' => 'Tech Solutions Ecuador SA',
            'ruc' => '1791234567001',
            'tradename' => 'TechSol EC',
            'matrixname' => 'Matriz Quito',
            'telefono' => '022333444',
            'correo_corporativo' => 'info@techsol.ec',
            'db_name' => '1791234567001',
        ]);

        $empresa2 = EmpresaModel::create([
            'nombre' => 'Comercial Andina Cia Ltda',
            'ruc' => '1798765432001',
            'tradename' => 'Andina Commerce',
            'matrixname' => 'Matriz Guayaquil',
            'telefono' => '042555666',
            'correo_corporativo' => 'contacto@andina.ec',
            'db_name' => '1798765432001',
        ]);

        $empresa3 = EmpresaModel::create([
            'nombre' => 'Inversiones del Valle SA',
            'ruc' => '1701234567001',
            'tradename' => 'Valle Investments',
            'matrixname' => 'Matriz Cuenca',
            'telefono' => '072777888',
            'correo_corporativo' => 'info@valleinv.ec',
            'db_name' => '1701234567001',
        ]);

        // === Usuarios ===
        $admin = UsuarioModel::create([
            'nombre' => 'Kevin Toasa',
            'email' => 'admin@billing.com',
            'password' => bcrypt('Admin123!'),
        ]);

        $gestor = UsuarioModel::create([
            'nombre' => 'Maria Perez',
            'email' => 'gestor@billing.com',
            'password' => bcrypt('Gestor123!'),
        ]);

        $visor = UsuarioModel::create([
            'nombre' => 'Juan Garcia',
            'email' => 'visor@billing.com',
            'password' => bcrypt('Visor123!'),
        ]);

        // === Asignar roles a usuarios ===
        $rolAdmin = RolModel::where('nombre', 'admin')->first();
        $rolGestor = RolModel::where('nombre', 'gestor')->first();
        $rolVisor = RolModel::where('nombre', 'visor')->first();

        $admin->roles()->attach($rolAdmin->id);
        $gestor->roles()->attach($rolGestor->id);
        $visor->roles()->attach($rolVisor->id);

        // === Asignar empresas a usuarios ===
        $admin->empresas()->attach([$empresa1->id, $empresa2->id, $empresa3->id]);
        $gestor->empresas()->attach([$empresa1->id, $empresa2->id]);
        $visor->empresas()->attach([$empresa1->id]);

        // === Menus ===
        $menuDashboard = MenuModel::create([
            'nombre' => 'Dashboard', 'ruta' => '/dashboard', 'icono' => 'pi pi-home', 'orden' => 1,
        ]);

        $menuEmpresas = MenuModel::create([
            'nombre' => 'Empresas', 'ruta' => '/empresas', 'icono' => 'pi pi-building', 'orden' => 2,
        ]);

        $menuEmpresasLista = MenuModel::create([
            'nombre' => 'Listar Empresas', 'ruta' => '/empresas/lista', 'icono' => 'pi pi-list',
            'parent_id' => $menuEmpresas->id, 'orden' => 1,
        ]);

        $menuEmpresasCrear = MenuModel::create([
            'nombre' => 'Crear Empresa', 'ruta' => '/empresas/crear', 'icono' => 'pi pi-plus',
            'parent_id' => $menuEmpresas->id, 'orden' => 2,
        ]);

        $menuUsuarios = MenuModel::create([
            'nombre' => 'Usuarios', 'ruta' => '/usuarios', 'icono' => 'pi pi-users', 'orden' => 3,
        ]);

        $menuUsuariosLista = MenuModel::create([
            'nombre' => 'Listar Usuarios', 'ruta' => '/usuarios/lista', 'icono' => 'pi pi-list',
            'parent_id' => $menuUsuarios->id, 'orden' => 1,
        ]);

        $menuUsuariosCrear = MenuModel::create([
            'nombre' => 'Crear Usuario', 'ruta' => '/usuarios/crear', 'icono' => 'pi pi-user-plus',
            'parent_id' => $menuUsuarios->id, 'orden' => 2,
        ]);

        $menuConfig = MenuModel::create([
            'nombre' => 'Configuracion', 'ruta' => '/configuracion', 'icono' => 'pi pi-cog', 'orden' => 4,
        ]);

        $menuConfigMenus = MenuModel::create([
            'nombre' => 'Menus', 'ruta' => '/configuracion/menus', 'icono' => 'pi pi-bars',
            'parent_id' => $menuConfig->id, 'orden' => 1,
        ]);

        $menuConfigRoles = MenuModel::create([
            'nombre' => 'Roles', 'ruta' => '/configuracion/roles', 'icono' => 'pi pi-id-card',
            'parent_id' => $menuConfig->id, 'orden' => 2,
        ]);

        // === Asignar menus a roles ===
        // Admin: todos los menus
        $todosMenus = MenuModel::all();
        foreach ($todosMenus as $menu) {
            $menu->roles()->attach($rolAdmin->id);
        }

        // Gestor: Dashboard, Empresas, Usuarios (lista y crear)
        $menusGestor = [
            $menuDashboard->id, $menuEmpresas->id, $menuEmpresasLista->id,
            $menuUsuarios->id, $menuUsuariosLista->id, $menuUsuariosCrear->id,
        ];
        foreach ($menusGestor as $menuId) {
            DB::table('menu_rol')->insertOrIgnore([
                'menu_id' => $menuId, 'rol_id' => $rolGestor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Visor: Dashboard y Empresas (solo lista)
        $menusVisor = [$menuDashboard->id, $menuEmpresas->id, $menuEmpresasLista->id];
        foreach ($menusVisor as $menuId) {
            DB::table('menu_rol')->insertOrIgnore([
                'menu_id' => $menuId, 'rol_id' => $rolVisor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
