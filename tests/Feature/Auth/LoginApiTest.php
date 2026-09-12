<?php

namespace Tests\Feature\Auth;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_con_credenciales_validas_devuelve_token(): void
    {
        $empresa = EmpresaModel::create([
            'nombre' => 'Empresa Test SA', 'ruc' => '1791234567001',
            'tradename' => 'Test Commerce', 'matrixname' => 'Matriz Quito',
            'telefono' => '023333333', 'correo_corporativo' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Test', 'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);
        $usuario->empresas()->attach($empresa->id);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@test.com', 'password' => 'password123',
            'enterprise_id' => $empresa->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'response' => [
                    'token',
                    'usuario' => ['id', 'nombre', 'email'],
                    'empresa' => ['id', 'nombre', 'ruc'],
                ],
            ])
            ->assertJsonPath('response.usuario.email', 'usuario@test.com')
            ->assertJsonPath('response.empresa.ruc', '1791234567001');

        $this->assertNotEmpty($response->json('response.token'));
    }

    public function test_login_con_credenciales_invalidas_devuelve_401(): void
    {
        $empresa = EmpresaModel::create([
            'nombre' => 'Empresa Test SA', 'ruc' => '1791234567001',
            'tradename' => 'Test Commerce', 'matrixname' => 'Matriz Quito',
            'telefono' => '023333333', 'correo_corporativo' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        UsuarioModel::create([
            'nombre' => 'Usuario Test', 'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@test.com', 'password' => 'wrong-password',
            'enterprise_id' => $empresa->id,
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false)
            ->assertJsonPath('response', 'Credenciales inválidas.');
    }

    public function test_login_con_empresa_no_asignada_devuelve_403(): void
    {
        $empresa1 = EmpresaModel::create([
            'nombre' => 'Empresa 1', 'ruc' => '1791234567001', 'tradename' => 'Test 1',
            'matrixname' => 'Matriz 1', 'telefono' => '023333333',
            'correo_corporativo' => 'info1@test.com', 'db_name' => '1791234567001',
        ]);
        $empresa2 = EmpresaModel::create([
            'nombre' => 'Empresa 2', 'ruc' => '1799876543001', 'tradename' => 'Test 2',
            'matrixname' => 'Matriz 2', 'telefono' => '024444444',
            'correo_corporativo' => 'info2@test.com', 'db_name' => '1799876543001',
        ]);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Test', 'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);
        $usuario->empresas()->attach($empresa1->id);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@test.com', 'password' => 'password123',
            'enterprise_id' => $empresa2->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('response', 'La empresa seleccionada no pertenece al usuario.');
    }

    public function test_login_sin_campos_requeridos_devuelve_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'enterprise_id']);
    }

    public function test_login_inicial_devuelve_usuario_y_empresas(): void
    {
        $empresa = EmpresaModel::create([
            'nombre' => 'Empresa Test SA', 'ruc' => '1791234567001',
            'tradename' => 'Test Commerce', 'matrixname' => 'Matriz Quito',
            'telefono' => '023333333', 'correo_corporativo' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Test', 'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);
        $usuario->empresas()->attach($empresa->id);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'usuario@test.com', 'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.usuario.email', 'usuario@test.com')
            ->assertJsonPath('response.empresas.0.ruc', '1791234567001');
    }
}
