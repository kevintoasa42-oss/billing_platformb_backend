<?php

namespace Database\Seeders;

use App\Context\Enterprise\Infrastructure\Eloquent\Models\EnterpriseModel;
use App\Context\Enterprise\Infrastructure\Eloquent\Models\RoleModel;
use App\Context\Enterprise\Infrastructure\Eloquent\Models\UserModel;
use App\Context\Menu\Infrastructure\Eloquent\Models\MenuModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Crea datos de prueba: enterprises, users, roles, menus y asignaciones.
     */
    public function run(): void
    {
        // === Enterprises ===
        $enterprise1 = EnterpriseModel::create([
            'nombre' => 'Tech Solutions Ecuador SA',
            'ruc' => '1791234567001',
            'tradename' => 'TechSol EC',
            'matrixname' => 'Matriz Quito',
            'telefono' => '022333444',
            'correo_corporativo' => 'info@techsol.ec',
            'db_name' => '1791234567001',
        ]);

        $enterprise2 = EnterpriseModel::create([
            'nombre' => 'Comercial Andina Cia Ltda',
            'ruc' => '1798765432001',
            'tradename' => 'Andina Commerce',
            'matrixname' => 'Matriz Guayaquil',
            'telefono' => '042555666',
            'correo_corporativo' => 'contacto@andina.ec',
            'db_name' => '1798765432001',
        ]);

        $enterprise3 = EnterpriseModel::create([
            'nombre' => 'Inversiones del Valle SA',
            'ruc' => '1701234567001',
            'tradename' => 'Valle Investments',
            'matrixname' => 'Matriz Cuenca',
            'telefono' => '072777888',
            'correo_corporativo' => 'info@valleinv.ec',
            'db_name' => '1701234567001',
        ]);

        // === Users ===
        $admin = UserModel::create([
            'nombre' => 'Kevin Toasa',
            'email' => 'admin@billing.com',
            'password' => bcrypt('Admin123!'),
        ]);

        $gestor = UserModel::create([
            'nombre' => 'Maria Perez',
            'email' => 'gestor@billing.com',
            'password' => bcrypt('Gestor123!'),
        ]);

        $visor = UserModel::create([
            'nombre' => 'Juan Garcia',
            'email' => 'visor@billing.com',
            'password' => bcrypt('Visor123!'),
        ]);

        // === Asignar roles a users ===
        $rolAdmin = RoleModel::where('nombre', 'admin')->first();
        $rolGestor = RoleModel::where('nombre', 'gestor')->first();
        $rolVisor = RoleModel::where('nombre', 'visor')->first();

        $admin->roles()->attach($rolAdmin->id);
        $gestor->roles()->attach($rolGestor->id);
        $visor->roles()->attach($rolVisor->id);

        // === Asignar enterprises a users ===
        $admin->enterprises()->attach([$enterprise1->id, $enterprise2->id, $enterprise3->id]);
        $gestor->enterprises()->attach([$enterprise1->id, $enterprise2->id]);
        $visor->enterprises()->attach([$enterprise1->id]);

        // === Menus ===
        $menuDashboard = MenuModel::create([
            'nombre' => 'Dashboard', 'ruta' => '/dashboard', 'icono' => 'pi pi-home', 'orden' => 1,
        ]);

        $menuEnterprises = MenuModel::create([
            'nombre' => 'Enterprises', 'ruta' => '/enterprises', 'icono' => 'pi pi-building', 'orden' => 2,
        ]);

        $menuEnterprisesLista = MenuModel::create([
            'nombre' => 'List Enterprises', 'ruta' => '/enterprises/list', 'icono' => 'pi pi-list',
            'parent_id' => $menuEnterprises->id, 'orden' => 1,
        ]);

        $menuEnterprisesCrear = MenuModel::create([
            'nombre' => 'Create Enterprise', 'ruta' => '/enterprises/create', 'icono' => 'pi pi-plus',
            'parent_id' => $menuEnterprises->id, 'orden' => 2,
        ]);

        $menuUsers = MenuModel::create([
            'nombre' => 'Users', 'ruta' => '/users', 'icono' => 'pi pi-users', 'orden' => 3,
        ]);

        $menuUsersLista = MenuModel::create([
            'nombre' => 'List Users', 'ruta' => '/users/list', 'icono' => 'pi pi-list',
            'parent_id' => $menuUsers->id, 'orden' => 1,
        ]);

        $menuUsersCrear = MenuModel::create([
            'nombre' => 'Create User', 'ruta' => '/users/create', 'icono' => 'pi pi-user-plus',
            'parent_id' => $menuUsers->id, 'orden' => 2,
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

        $menuProducts = MenuModel::create([
            'nombre' => 'Products', 'ruta' => '/products', 'icono' => 'pi pi-box', 'orden' => 5,
        ]);

        $menuProductsLista = MenuModel::create([
            'nombre' => 'List Products', 'ruta' => '/products/list', 'icono' => 'pi pi-list',
            'parent_id' => $menuProducts->id, 'orden' => 1,
        ]);

        $menuProductsCrear = MenuModel::create([
            'nombre' => 'Create Product', 'ruta' => '/products/create', 'icono' => 'pi pi-plus',
            'parent_id' => $menuProducts->id, 'orden' => 2,
        ]);

        // === Asignar menus a roles ===
        // Admin: todos los menus
        $todosMenus = MenuModel::all();
        foreach ($todosMenus as $menu) {
            $menu->roles()->attach($rolAdmin->id);
        }

        // Gestor: Dashboard, Enterprises, Users, Products
        $menusGestor = [
            $menuDashboard->id, $menuEnterprises->id, $menuEnterprisesLista->id,
            $menuUsers->id, $menuUsersLista->id, $menuUsersCrear->id,
            $menuProducts->id, $menuProductsLista->id, $menuProductsCrear->id,
        ];
        foreach ($menusGestor as $menuId) {
            DB::table('menu_role')->insertOrIgnore([
                'menu_id' => $menuId, 'rol_id' => $rolGestor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Visor: Dashboard y Enterprises (solo lista)
        $menusVisor = [$menuDashboard->id, $menuEnterprises->id, $menuEnterprisesLista->id];
        foreach ($menusVisor as $menuId) {
            DB::table('menu_role')->insertOrIgnore([
                'menu_id' => $menuId, 'rol_id' => $rolVisor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
