<?php

namespace Tests\Feature\Enterprise;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\RolModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmpresaApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * POST /api/empresas crea una empresa y devuelve 201 con los datos.
     */
    public function test_crear_empresa_devuelve_201_con_datos(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/empresas', [
            'nombre' => 'Mi Empresa SA',
            'ruc' => '1791234567001',
            'tradename' => 'Mi Commerce',
            'matrixname' => 'Matriz Guayaquil',
            'telefono' => '029999999',
            'correo_corporativo' => 'contacto@miempresa.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('nombre', 'Mi Empresa SA')
            ->assertJsonPath('ruc', '1791234567001')
            ->assertJsonPath('tradename', 'Mi Commerce')
            ->assertJsonPath('matrixname', 'Matriz Guayaquil')
            ->assertJsonPath('telefono', '029999999')
            ->assertJsonPath('correo_corporativo', 'contacto@miempresa.com');

        $this->assertDatabaseHas('enterprises', [
            'ruc' => '1791234567001',
            'nombre' => 'Mi Empresa SA',
        ]);
    }

    /**
     * POST /api/empresas con RUC duplicado devuelve 422.
     */
    public function test_crear_empresa_con_ruc_duplicado_devuelve_422(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        EmpresaModel::create([
            'nombre' => 'Empresa Existente',
            'ruc' => '1791234567001',
            'tradename' => 'Existente',
            'matrixname' => 'Matriz',
            'telefono' => '023333333',
            'correo_corporativo' => 'existente@test.com',
            'db_name' => '1791234567001',
        ]);

        $response = $this->postJson('/api/empresas', [
            'nombre' => 'Otra Empresa',
            'ruc' => '1791234567001',
            'tradename' => 'Otra',
            'matrixname' => 'Matriz 2',
            'telefono' => '024444444',
            'correo_corporativo' => 'otra@test.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);
    }

    /**
     * POST /api/empresas sin auth devuelve 401.
     */
    public function test_crear_empresa_sin_auth_devuelve_401(): void
    {
        $response = $this->postJson('/api/empresas', [
            'nombre' => 'Mi Empresa SA',
            'ruc' => '1791234567001',
            'tradename' => 'Mi Commerce',
            'matrixname' => 'Matriz',
            'telefono' => '029999999',
            'correo_corporativo' => 'contacto@test.com',
        ]);

        $response->assertStatus(401);
    }

    /**
     * POST /api/empresas sin campos requeridos devuelve 422.
     */
    public function test_crear_empresa_sin_campos_requeridos_devuelve_422(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/empresas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'nombre', 'ruc', 'tradename', 'matrixname', 'telefono', 'correo_corporativo',
            ]);
    }

    /**
     * GET /api/empresas lista todas las empresas.
     */
    public function test_listar_empresas_devuelve_lista(): void
    {
        $user = UsuarioModel::create([
            'nombre' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($user);

        EmpresaModel::create([
            'nombre' => 'Empresa A',
            'ruc' => '1791111111001',
            'tradename' => 'A Commerce',
            'matrixname' => 'Matriz A',
            'telefono' => '021111111',
            'correo_corporativo' => 'a@test.com',
            'db_name' => '1791111111001',
        ]);

        EmpresaModel::create([
            'nombre' => 'Empresa B',
            'ruc' => '1792222222001',
            'tradename' => 'B Commerce',
            'matrixname' => 'Matriz B',
            'telefono' => '022222222',
            'correo_corporativo' => 'b@test.com',
            'db_name' => '1792222222001',
        ]);

        $response = $this->getJson('/api/empresas');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.nombre', 'Empresa A')
            ->assertJsonPath('1.nombre', 'Empresa B');
    }

    /**
     * GET /api/empresas sin empresas devuelve array vacio.
     */
    public function test_listar_empresas_sin_empresas_devuelve_vacio(): void
    {
        Sanctum::actingAs(
            UsuarioModel::create([
                'nombre' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->getJson('/api/empresas');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }
}
