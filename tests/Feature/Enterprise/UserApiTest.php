<?php

namespace Tests\Feature\Enterprise;

use App\Models\EnterpriseModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

class UserApiTest extends FeatureTestCase
{

    public function test_crear_user_devuelve_201_con_datos(): void
    {
        Sanctum::actingAs(
            UserModel::create([
                'name' => 'Admin', 'email' => 'admin@test.com',
                'password' => bcrypt('password123'),
            ])
        );

        $response = $this->postJson('/api/users', [
            'name' => 'Nuevo User', 'email' => 'nuevo@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.name', 'Nuevo User')
            ->assertJsonPath('response.email', 'nuevo@test.com');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@test.com', 'name' => 'Nuevo User',
        ]);
    }

    public function test_crear_user_con_email_duplicado_devuelve_422(): void
    {
        $existing = UserModel::create([
            'name' => 'Existente', 'email' => 'existente@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($existing);

        $response = $this->postJson('/api/users', [
            'name' => 'Otro', 'email' => 'existente@test.com', 'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_asignar_rol_a_user_devuelve_200(): void
    {
        $admin = UserModel::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $user = UserModel::create([
            'name' => 'User Target', 'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $rol = RoleModel::create([
            'name' => 'editor', 'description' => 'Editor de contenido',
        ]);

        $response = $this->postJson("/api/users/{$user->id}/roles", [
            'role_id' => $rol->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response', 'Role asignado correctamente.');

        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id, 'role_id' => $rol->id,
        ]);
    }

    public function test_asignar_rol_inexistente_devuelve_422(): void
    {
        $admin = UserModel::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $user = UserModel::create([
            'name' => 'User Target', 'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson("/api/users/{$user->id}/roles", [
            'role_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    public function test_asignar_enterprise_a_user_devuelve_200(): void
    {
        $admin = UserModel::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $user = UserModel::create([
            'name' => 'User Target', 'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $enterprise = EnterpriseModel::create([
            'name' => 'Enterprise SA', 'ruc' => '1791234567001',
            'tradename' => 'Commerce', 'matrix_name' => 'Matriz',
            'phone' => '023333333', 'corporate_email' => 'info@test.com',
            'db_name' => '1791234567001',
        ]);

        $response = $this->postJson("/api/users/{$user->id}/enterprises", [
            'enterprise_id' => $enterprise->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response', 'Enterprise asignada correctamente.');

        $this->assertDatabaseHas('user_enterprise', [
            'user_id' => $user->id, 'enterprise_id' => $enterprise->id,
        ]);
    }

    public function test_asignar_enterprise_inexistente_devuelve_422(): void
    {
        $admin = UserModel::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        Sanctum::actingAs($admin);

        $user = UserModel::create([
            'name' => 'User Target', 'email' => 'target@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson("/api/users/{$user->id}/enterprises", [
            'enterprise_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['enterprise_id']);
    }
}
