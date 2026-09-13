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
            'name' => 'Tech Solutions Ecuador SA',
            'ruc' => '1791234567001',
            'tradename' => 'TechSol EC',
            'matrix_name' => 'Matriz Quito',
            'phone' => '022333444',
            'corporate_email' => 'info@techsol.ec',
            'db_name' => '1791234567001',
        ]);

        $enterprise2 = EnterpriseModel::create([
            'name' => 'Comercial Andina Cia Ltda',
            'ruc' => '1798765432001',
            'tradename' => 'Andina Commerce',
            'matrix_name' => 'Matriz Guayaquil',
            'phone' => '042555666',
            'corporate_email' => 'contacto@andina.ec',
            'db_name' => '1798765432001',
        ]);

        $enterprise3 = EnterpriseModel::create([
            'name' => 'Inversiones del Valle SA',
            'ruc' => '1701234567001',
            'tradename' => 'Valle Investments',
            'matrix_name' => 'Matriz Cuenca',
            'phone' => '072777888',
            'corporate_email' => 'info@valleinv.ec',
            'db_name' => '1701234567001',
        ]);

        // === Users ===
        $admin = UserModel::create([
            'name' => 'Kevin Toasa',
            'email' => 'admin@billing.com',
            'password' => bcrypt('Admin123!'),
        ]);

        $gestor = UserModel::create([
            'name' => 'Maria Perez',
            'email' => 'gestor@billing.com',
            'password' => bcrypt('Gestor123!'),
        ]);

        $visor = UserModel::create([
            'name' => 'Juan Garcia',
            'email' => 'visor@billing.com',
            'password' => bcrypt('Visor123!'),
        ]);

        // === Asignar roles a users ===
        $rolAdmin = RoleModel::where('name', 'admin')->first();
        $rolGestor = RoleModel::where('name', 'gestor')->first();
        $rolVisor = RoleModel::where('name', 'visor')->first();

        $admin->roles()->attach($rolAdmin->id);
        $gestor->roles()->attach($rolGestor->id);
        $visor->roles()->attach($rolVisor->id);

        // === Asignar enterprises a users ===
        $admin->enterprises()->attach([$enterprise1->id, $enterprise2->id, $enterprise3->id]);
        $gestor->enterprises()->attach([$enterprise1->id, $enterprise2->id]);
        $visor->enterprises()->attach([$enterprise1->id]);

        // === Menus ===
        $menuDashboard = MenuModel::create([
            'name' => 'Dashboard', 'route' => '/dashboard', 'icon' => 'pi pi-home', 'order' => 1,
        ]);

        $menuEnterprises = MenuModel::create([
            'name' => 'Enterprises', 'route' => '/enterprises', 'icon' => 'pi pi-building', 'order' => 2,
        ]);

        $menuEnterprisesLista = MenuModel::create([
            'name' => 'List Enterprises', 'route' => '/enterprises/list', 'icon' => 'pi pi-list',
            'parent_id' => $menuEnterprises->id, 'order' => 1,
        ]);

        $menuEnterprisesCrear = MenuModel::create([
            'name' => 'Create Enterprise', 'route' => '/enterprises/create', 'icon' => 'pi pi-plus',
            'parent_id' => $menuEnterprises->id, 'order' => 2,
        ]);

        $menuUsers = MenuModel::create([
            'name' => 'Users', 'route' => '/users', 'icon' => 'pi pi-users', 'order' => 3,
        ]);

        $menuUsersLista = MenuModel::create([
            'name' => 'List Users', 'route' => '/users/list', 'icon' => 'pi pi-list',
            'parent_id' => $menuUsers->id, 'order' => 1,
        ]);

        $menuUsersCrear = MenuModel::create([
            'name' => 'Create User', 'route' => '/users/create', 'icon' => 'pi pi-user-plus',
            'parent_id' => $menuUsers->id, 'order' => 2,
        ]);

        $menuConfig = MenuModel::create([
            'name' => 'Configuracion', 'route' => '/configuracion', 'icon' => 'pi pi-cog', 'order' => 4,
        ]);

        $menuConfigMenus = MenuModel::create([
            'name' => 'Menus', 'route' => '/configuracion/menus', 'icon' => 'pi pi-bars',
            'parent_id' => $menuConfig->id, 'order' => 1,
        ]);

        $menuConfigRoles = MenuModel::create([
            'name' => 'Roles', 'route' => '/configuracion/roles', 'icon' => 'pi pi-id-card',
            'parent_id' => $menuConfig->id, 'order' => 2,
        ]);

        $menuProducts = MenuModel::create([
            'name' => 'Products', 'route' => '/products', 'icon' => 'pi pi-box', 'order' => 5,
        ]);

        $menuProductsLista = MenuModel::create([
            'name' => 'List Products', 'route' => '/products/list', 'icon' => 'pi pi-list',
            'parent_id' => $menuProducts->id, 'order' => 1,
        ]);

        $menuProductsCrear = MenuModel::create([
            'name' => 'Create Product', 'route' => '/products/create', 'icon' => 'pi pi-plus',
            'parent_id' => $menuProducts->id, 'order' => 2,
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
                'menu_id' => $menuId, 'role_id' => $rolGestor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Visor: Dashboard y Enterprises (solo lista)
        $menusVisor = [$menuDashboard->id, $menuEnterprises->id, $menuEnterprisesLista->id];
        foreach ($menusVisor as $menuId) {
            DB::table('menu_role')->insertOrIgnore([
                'menu_id' => $menuId, 'role_id' => $rolVisor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
