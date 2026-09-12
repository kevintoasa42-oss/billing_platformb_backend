<?php

namespace Tests\Feature\Menu;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\RolModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use App\Contexto\Menu\Infraestructura\Eloquent\Models\MenuModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MenuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_menu_padre_devuelve_201(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/menus', [
            'nombre' => 'Dashboard', 'ruta' => '/dashboard',
            'icono' => 'home', 'orden' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.nombre', 'Dashboard')
            ->assertJsonPath('response.ruta', '/dashboard')
            ->assertJsonPath('response.icono', 'home');

        $this->assertDatabaseHas('menus', [
            'nombre' => 'Dashboard', 'ruta' => '/dashboard',
        ]);
    }

    public function test_crear_submenu_con_parent_id_devuelve_201(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $padre = MenuModel::create([
            'nombre' => 'Configuracion', 'ruta' => '/config',
            'icono' => 'settings', 'orden' => 1,
        ]);

        $response = $this->postJson('/api/menus', [
            'nombre' => 'Usuarios', 'ruta' => '/config/usuarios',
            'icono' => 'users', 'parent_id' => $padre->id, 'orden' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.nombre', 'Usuarios')
            ->assertJsonPath('response.parent_id', $padre->id);

        $this->assertDatabaseHas('menus', [
            'nombre' => 'Usuarios', 'parent_id' => $padre->id,
        ]);
    }

    public function test_crear_menu_sin_nombre_devuelve_422(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/menus', ['ruta' => '/test']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_listar_menus_devuelve_jerarquia(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $padre = MenuModel::create([
            'nombre' => 'Dashboard', 'ruta' => '/dashboard',
            'icono' => 'home', 'orden' => 1,
        ]);
        MenuModel::create([
            'nombre' => 'Reportes', 'ruta' => '/dashboard/reportes',
            'icono' => 'chart', 'parent_id' => $padre->id, 'orden' => 1,
        ]);

        $response = $this->getJson('/api/menus');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'response')
            ->assertJsonPath('response.0.nombre', 'Dashboard')
            ->assertJsonPath('response.0.hijos.0.nombre', 'Reportes');
    }

    public function test_asignar_menu_a_rol_devuelve_200(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $menu = MenuModel::create([
            'nombre' => 'Dashboard', 'ruta' => '/dashboard',
            'icono' => 'home', 'orden' => 1,
        ]);
        $rol = RolModel::create([
            'nombre' => 'admin', 'descripcion' => 'Administrador',
        ]);

        $response = $this->postJson("/api/menus/{$menu->id}/roles", [
            'rol_id' => $rol->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response', 'Menu asignado al rol correctamente.');

        $this->assertDatabaseHas('menu_rol', [
            'menu_id' => $menu->id, 'rol_id' => $rol->id,
        ]);
    }

    public function test_asignar_menu_a_rol_inexistente_devuelve_422(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $menu = MenuModel::create([
            'nombre' => 'Dashboard', 'ruta' => '/dashboard',
            'icono' => 'home', 'orden' => 1,
        ]);

        $response = $this->postJson("/api/menus/{$menu->id}/roles", [
            'rol_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rol_id']);
    }

    public function test_obtener_menus_por_rol_devuelve_menus_asignados(): void
    {
        $user = UsuarioModel::create([
            'nombre' => 'Usuario Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($user);

        $rol = RolModel::create([
            'nombre' => 'visor', 'descripcion' => 'Solo lectura',
        ]);
        $user->roles()->attach($rol->id);

        $menuPadre = MenuModel::create([
            'nombre' => 'Dashboard', 'ruta' => '/dashboard',
            'icono' => 'home', 'orden' => 1,
        ]);
        $menuHijo = MenuModel::create([
            'nombre' => 'Reportes', 'ruta' => '/dashboard/reportes',
            'icono' => 'chart', 'parent_id' => $menuPadre->id, 'orden' => 1,
        ]);

        $menuPadre->roles()->attach($rol->id);
        $menuHijo->roles()->attach($rol->id);

        $response = $this->getJson('/api/menus/por-rol');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'response')
            ->assertJsonPath('response.0.nombre', 'Dashboard')
            ->assertJsonPath('response.0.hijos.0.nombre', 'Reportes');
    }

    public function test_obtener_menus_por_rol_sin_roles_devuelve_vacio(): void
    {
        $user = UsuarioModel::create([
            'nombre' => 'Usuario Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/menus/por-rol');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(0, 'response');
    }

    public function test_obtener_menus_por_rol_sin_auth_devuelve_401(): void
    {
        $response = $this->getJson('/api/menus/por-rol');

        $response->assertStatus(401);
    }
}
