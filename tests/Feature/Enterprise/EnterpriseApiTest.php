<?php

namespace Tests\Feature\Enterprise;

use App\Models\EnterpriseModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnterpriseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_enterprise_devuelve_201_con_datos(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/enterprises', [
            'name' => 'Mi Enterprise SA', 'ruc' => '1791234567001',
            'tradename' => 'Mi Commerce', 'matrix_name' => 'Matriz Guayaquil',
            'phone' => '029999999', 'corporate_email' => 'contacto@mienterprise.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.name', 'Mi Enterprise SA')
            ->assertJsonPath('response.ruc', '1791234567001')
            ->assertJsonPath('response.tradename', 'Mi Commerce')
            ->assertJsonPath('response.matrix_name', 'Matriz Guayaquil')
            ->assertJsonPath('response.phone', '029999999')
            ->assertJsonPath('response.corporate_email', 'contacto@mienterprise.com');

        $this->assertDatabaseHas('enterprises', [
            'ruc' => '1791234567001', 'name' => 'Mi Enterprise SA',
        ]);
    }

    public function test_crear_enterprise_con_ruc_duplicado_devuelve_422(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        EnterpriseModel::create([
            'name' => 'Enterprise Existente', 'ruc' => '1791234567001',
            'tradename' => 'Existente', 'matrix_name' => 'Matriz',
            'phone' => '023333333', 'corporate_email' => 'existente@test.com',
            'db_name' => '1791234567001',
        ]);

        $response = $this->postJson('/api/enterprises', [
            'name' => 'Otra Enterprise', 'ruc' => '1791234567001',
            'tradename' => 'Otra', 'matrix_name' => 'Matriz 2',
            'phone' => '024444444', 'corporate_email' => 'otra@test.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);
    }

    public function test_crear_enterprise_sin_auth_devuelve_401(): void
    {
        $response = $this->postJson('/api/enterprises', [
            'name' => 'Mi Enterprise SA', 'ruc' => '1791234567001',
            'tradename' => 'Mi Commerce', 'matrix_name' => 'Matriz',
            'phone' => '029999999', 'corporate_email' => 'contacto@test.com',
        ]);

        $response->assertStatus(401);
    }

    public function test_crear_enterprise_sin_campos_requeridos_devuelve_422(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/enterprises', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name', 'ruc', 'tradename', 'matrix_name', 'phone', 'corporate_email',
            ]);
    }

    public function test_listar_enterprises_devuelve_lista(): void
    {
        $user = UserModel::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($user);

        EnterpriseModel::create([
            'name' => 'Enterprise A', 'ruc' => '1791111111001', 'tradename' => 'A Commerce',
            'matrix_name' => 'Matriz A', 'phone' => '021111111',
            'corporate_email' => 'a@test.com', 'db_name' => '1791111111001',
        ]);
        EnterpriseModel::create([
            'name' => 'Enterprise B', 'ruc' => '1792222222001', 'tradename' => 'B Commerce',
            'matrix_name' => 'Matriz B', 'phone' => '022222222',
            'corporate_email' => 'b@test.com', 'db_name' => '1792222222001',
        ]);

        $response = $this->getJson('/api/enterprises');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'response')
            ->assertJsonPath('response.0.name', 'Enterprise A')
            ->assertJsonPath('response.1.name', 'Enterprise B');
    }

    public function test_listar_enterprises_sin_enterprises_devuelve_vacio(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->getJson('/api/enterprises');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(0, 'response');
    }
}
