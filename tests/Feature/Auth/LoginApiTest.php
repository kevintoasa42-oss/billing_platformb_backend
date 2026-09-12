<?php

namespace Tests\Feature\Auth;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\RolModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * POST /api/login con credenciales validas devuelve token y datos del usuario/empresa.
     */
    public function test_login_con_credenciales_validas_devuelve_token(): void
    {
        // Crear empresa.
        $empresa = EmpresaModel::create([
            'nombre' => 'Empresa Test SA',
            'ruc' => '1791234567001',
            'tradename' => 'Test Commerce',
            'matrixname' => 'Matriz Quito',
            'telefono' => '023333333',
            'correo_corporativo' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        // Crear usuario.
        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Test',
            'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);

        // Asignar empresa al usuario.
        $usuario->empresas()->attach($empresa->id);

        // Hacer login.
        $response = $this->postJson('/api/login', [
            'email' => 'usuario@test.com',
            'password' => 'password123',
            'enterprise_id' => $empresa->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'usuario' => ['id', 'nombre', 'email'],
                'empresa' => ['id', 'nombre', 'ruc'],
            ])
            ->assertJsonPath('usuario.email', 'usuario@test.com')
            ->assertJsonPath('empresa.ruc', '1791234567001');

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * POST /api/login con credenciales invalidas devuelve 401.
     */
    public function test_login_con_credenciales_invalidas_devuelve_401(): void
    {
        $empresa = EmpresaModel::create([
            'nombre' => 'Empresa Test SA',
            'ruc' => '1791234567001',
            'tradename' => 'Test Commerce',
            'matrixname' => 'Matriz Quito',
            'telefono' => '023333333',
            'correo_corporativo' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        UsuarioModel::create([
            'nombre' => 'Usuario Test',
            'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@test.com',
            'password' => 'wrong-password',
            'enterprise_id' => $empresa->id,
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas.');
    }

    /**
     * POST /api/login con empresa no asignada al usuario devuelve 403.
     */
    public function test_login_con_empresa_no_asignada_devuelve_403(): void
    {
        $empresa1 = EmpresaModel::create([
            'nombre' => 'Empresa 1',
            'ruc' => '1791234567001',
            'tradename' => 'Test 1',
            'matrixname' => 'Matriz 1',
            'telefono' => '023333333',
            'correo_corporativo' => 'info1@test.com',
            'db_name' => '1791234567001',
        ]);

        $empresa2 = EmpresaModel::create([
            'nombre' => 'Empresa 2',
            'ruc' => '1799876543001',
            'tradename' => 'Test 2',
            'matrixname' => 'Matriz 2',
            'telefono' => '024444444',
            'correo_corporativo' => 'info2@test.com',
            'db_name' => '1799876543001',
        ]);

        $usuario = UsuarioModel::create([
            'nombre' => 'Usuario Test',
            'email' => 'usuario@test.com',
            'password' => bcrypt('password123'),
        ]);

        // Solo asignar empresa1 al usuario, intentar login con empresa2.
        $usuario->empresas()->attach($empresa1->id);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@test.com',
            'password' => 'password123',
            'enterprise_id' => $empresa2->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'La empresa seleccionada no pertenece al usuario.');
    }

    /**
     * POST /api/login sin campos requeridos devuelve 422.
     */
    public function test_login_sin_campos_requeridos_devuelve_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'enterprise_id']);
    }
}
