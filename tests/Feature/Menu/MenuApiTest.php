<?php

namespace Tests\Feature\Menu;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\MenuModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MenuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_menu_padre_devuelve_201(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/menus', [
            'name' => 'Dashboard', 'route' => '/dashboard',
            'icon' => 'home', 'order' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.label', 'Dashboard')
            ->assertJsonPath('response.href', '/dashboard')
            ->assertJsonPath('response.icon', 'home');

        $this->assertDatabaseHas('menus', [
            'name' => 'Dashboard', 'route' => '/dashboard',
        ]);
    }

    public function test_crear_submenu_con_parent_id_devuelve_201(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $padre = MenuModel::create([
            'name' => 'Configuracion', 'route' => '/config',
            'icon' => 'settings', 'order' => 1,
        ]);

        $response = $this->postJson('/api/menus', [
            'name' => 'Users', 'route' => '/config/users',
            'icon' => 'users', 'parent_id' => $padre->id, 'order' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.label', 'Users')
            ->assertJsonPath('response.children', []);

        $this->assertDatabaseHas('menus', [
            'name' => 'Users', 'parent_id' => $padre->id,
        ]);
    }

    public function test_crear_menu_sin_nombre_devuelve_422(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/menus', ['route' => '/test']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_listar_menus_devuelve_jerarquia(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $padre = MenuModel::create([
            'name' => 'Dashboard', 'route' => '/dashboard',
            'icon' => 'home', 'order' => 1,
        ]);
        MenuModel::create([
            'name' => 'Reportes', 'route' => '/dashboard/reportes',
            'icon' => 'chart', 'parent_id' => $padre->id, 'order' => 1,
        ]);

        $response = $this->getJson('/api/menus');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'response')
            ->assertJsonPath('response.0.label', 'Dashboard')
            ->assertJsonPath('response.0.children.0.label', 'Reportes');
    }

    public function test_asignar_menu_a_rol_devuelve_200(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $menu = MenuModel::create([
            'name' => 'Dashboard', 'route' => '/dashboard',
            'icon' => 'home', 'order' => 1,
        ]);
        $rol = RoleModel::create([
            'name' => 'admin', 'description' => 'Administrador',
        ]);

        $response = $this->postJson("/api/menus/{$menu->id}/roles", [
            'role_id' => $rol->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response', 'Menu asignado al rol correctamente.');

        $this->assertDatabaseHas('menu_role', [
            'menu_id' => $menu->id, 'role_id' => $rol->id,
        ]);
    }

    public function test_asignar_menu_a_rol_inexistente_devuelve_422(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $menu = MenuModel::create([
            'name' => 'Dashboard', 'route' => '/dashboard',
            'icon' => 'home', 'order' => 1,
        ]);

        $response = $this->postJson("/api/menus/{$menu->id}/roles", [
            'role_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    public function test_obtener_menus_por_rol_devuelve_menus_asignados(): void
    {
        $user = UserModel::create([
            'name' => 'User Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($user);

        $rol = RoleModel::create([
            'name' => 'visor', 'description' => 'Solo lectura',
        ]);
        $user->roles()->attach($rol->id);

        $menuPadre = MenuModel::create([
            'name' => 'Dashboard', 'route' => '/dashboard',
            'icon' => 'home', 'order' => 1,
        ]);
        $menuHijo = MenuModel::create([
            'name' => 'Reportes', 'route' => '/dashboard/reportes',
            'icon' => 'chart', 'parent_id' => $menuPadre->id, 'order' => 1,
        ]);

        $menuPadre->roles()->attach($rol->id);
        $menuHijo->roles()->attach($rol->id);

        $response = $this->getJson('/api/menus/by-role');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'response')
            ->assertJsonPath('response.0.label', 'Dashboard')
            ->assertJsonPath('response.0.children.0.label', 'Reportes');
    }

    public function test_obtener_menus_por_rol_sin_roles_devuelve_vacio(): void
    {
        $user = UserModel::create([
            'name' => 'User Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/menus/by-role');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(0, 'response');
    }

    public function test_obtener_menus_por_rol_sin_auth_devuelve_401(): void
    {
        $response = $this->getJson('/api/menus/by-role');

        $response->assertStatus(401);
    }
}
