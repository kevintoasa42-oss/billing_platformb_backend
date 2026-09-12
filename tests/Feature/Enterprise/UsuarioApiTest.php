<?php

namespace Tests\Feature\Enterprise;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\RolModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsuarioApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * POST /api/usuarios crea un usuario y devuelve 201.
     */
    public function test_crear_usuario_devuelve_201_con_datos(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/usuarios', [
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('nombre', 'Nuevo Usuario')
            ->assertJsonPath('email', 'nuevo@test.com');

        $this->assertDatabaseHas('usuarios', [
            'email' => 'nuevo@test.com',
            'nombre' => 'Nuevo Usuario',
        ]);
    }

    /**
     * POST /api/usuarios con email duplicado devuelve 422.
     */
    public function test_crear_usuario_con_email_duplicado_devuelve_422(): void
    {
        $existing = UsuarioModel::create([
            'nombre' => 'Existente',
            'email' => 'existente@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($existing);

        $response = $this->postJson('/api/usuarios', [
            'nombre' => 'Otro',
            'email' => 'existente@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * POST /api/usuarios/{id}/roles asigna un rol al usuario.
     */
    public function test_asignar_rol_a_usuario_devuelve_200(): void
    {
        $admin = UsuarioModel::create([
            'nombre' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Target',
            'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $rol = RolModel::create([
            'nombre' => 'editor',
            'descripcion' => 'Editor de contenido',
        ]);

        $response = $this->postJson("/api/usuarios/{$usuario->id}/roles", [
            'rol_id' => $rol->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Rol asignado correctamente.');

        $this->assertDatabaseHas('usuario_rol', [
            'usuario_id' => $usuario->id,
            'rol_id' => $rol->id,
        ]);
    }

    /**
     * POST /api/usuarios/{id}/roles con rol inexistente devuelve 422.
     */
    public function test_asignar_rol_inexistente_devuelve_422(): void
    {
        $admin = UsuarioModel::create([
            'nombre' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Target',
            'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson("/api/usuarios/{$usuario->id}/roles", [
            'rol_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rol_id']);
    }

    /**
     * POST /api/usuarios/{id}/empresas asigna una empresa al usuario.
     */
    public function test_asignar_empresa_a_usuario_devuelve_200(): void
    {
        $admin = UsuarioModel::create([
            'nombre' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Target',
            'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $empresa = EmpresaModel::create([
            'nombre' => 'Empresa SA',
            'ruc' => '1791234567001',
            'tradename' => 'Commerce',
            'matrixname' => 'Matriz',
            'telefono' => '023333333',
            'correo_corporativo' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        $response = $this->postJson("/api/usuarios/{$usuario->id}/empresas", [
            'enterprise_id' => $empresa->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Empresa asignada correctamente.');

        $this->assertDatabaseHas('usuario_empresa', [
            'usuario_id' => $usuario->id,
            'enterprise_id' => $empresa->id,
        ]);
    }

    /**
     * POST /api/usuarios/{id}/empresas con empresa inexistente devuelve 422.
     */
    public function test_asignar_empresa_inexistente_devuelve_422(): void
    {
        $admin = UsuarioModel::create([
            'nombre' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Target',
            'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson("/api/usuarios/{$usuario->id}/empresas", [
            'enterprise_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['enterprise_id']);
    }
}
