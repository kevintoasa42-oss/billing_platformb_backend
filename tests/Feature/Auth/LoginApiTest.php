<?php

namespace Tests\Feature\Auth;

use App\Models\EnterpriseModel;
use App\Models\UserModel;
use Tests\FeatureTestCase;

class LoginApiTest extends FeatureTestCase
{

    public function test_login_con_credenciales_validas_devuelve_token(): void
    {
        $enterprise = EnterpriseModel::create([
            'name' => 'Enterprise Test SA', 'ruc' => '1791234567001',
            'tradename' => 'Test Commerce', 'matrix_name' => 'Matriz Quito',
            'phone' => '023333333', 'corporate_email' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        $user = UserModel::create([
            'name' => 'User Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        $user->enterprises()->attach($enterprise->id);

        $response = $this->postJson('/api/login', [
            'email' => 'user@test.com', 'password' => 'password123',
            'enterprise_id' => $enterprise->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'response' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                    'enterprise' => ['id', 'name', 'ruc'],
                ],
            ])
            ->assertJsonPath('response.user.email', 'user@test.com')
            ->assertJsonPath('response.enterprise.ruc', '1791234567001');

        $this->assertNotEmpty($response->json('response.token'));
    }

    public function test_login_con_credenciales_invalidas_devuelve_401(): void
    {
        $enterprise = EnterpriseModel::create([
            'name' => 'Enterprise Test SA', 'ruc' => '1791234567001',
            'tradename' => 'Test Commerce', 'matrix_name' => 'Matriz Quito',
            'phone' => '023333333', 'corporate_email' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        UserModel::create([
            'name' => 'User Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@test.com', 'password' => 'wrong-password',
            'enterprise_id' => $enterprise->id,
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false)
            ->assertJsonPath('response', 'Credenciales inválidas.');
    }

    public function test_login_con_enterprise_no_asignada_devuelve_403(): void
    {
        $enterprise1 = EnterpriseModel::create([
            'name' => 'Enterprise 1', 'ruc' => '1791234567001', 'tradename' => 'Test 1',
            'matrix_name' => 'Matriz 1', 'phone' => '023333333',
            'corporate_email' => 'info1@test.com', 'db_name' => '1791234567001',
        ]);
        $enterprise2 = EnterpriseModel::create([
            'name' => 'Enterprise 2', 'ruc' => '1799876543001', 'tradename' => 'Test 2',
            'matrix_name' => 'Matriz 2', 'phone' => '024444444',
            'corporate_email' => 'info2@test.com', 'db_name' => '1799876543001',
        ]);

        $user = UserModel::create([
            'name' => 'User Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        $user->enterprises()->attach($enterprise1->id);

        $response = $this->postJson('/api/login', [
            'email' => 'user@test.com', 'password' => 'password123',
            'enterprise_id' => $enterprise2->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('response', 'La enterprise seleccionada no pertenece al user.');
    }

    public function test_login_sin_campos_requeridos_devuelve_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'enterprise_id']);
    }

    public function test_login_inicial_devuelve_user_y_enterprises(): void
    {
        $enterprise = EnterpriseModel::create([
            'name' => 'Enterprise Test SA', 'ruc' => '1791234567001',
            'tradename' => 'Test Commerce', 'matrix_name' => 'Matriz Quito',
            'phone' => '023333333', 'corporate_email' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        $user = UserModel::create([
            'name' => 'User Test', 'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        $user->enterprises()->attach($enterprise->id);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@test.com', 'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.user.email', 'user@test.com')
            ->assertJsonPath('response.enterprises.0.ruc', '1791234567001');
    }
}
