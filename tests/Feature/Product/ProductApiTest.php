<?php

namespace Tests\Feature\Product;

use App\Models\EnterpriseModel;
use App\Models\UserModel;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    private string $token;
    private int $iva15Id;

    protected function setUp(): void
    {
        parent::setUp();

        // Resetear DB central
        $this->artisan('migrate:fresh');

        // Resetear DB tenant si existe
        $this->dropTenantDb('1790000000001');

        // Insertar catalogo de IVA
        \DB::connection('pgsql')->table('sri_iva_percentages')->insert([
            ['code' => 'IVA_15', 'name' => 'Tarifa general', 'percentage' => 15.00, 'description' => 'IVA general', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'IVA_0', 'name' => 'Tarifa cero', 'percentage' => 0.00, 'description' => 'IVA 0%', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insertar roles
        \DB::connection('pgsql')->table('roles')->insert([
            ['name' => 'admin', 'description' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'gestor', 'description' => 'Gestor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'visor', 'description' => 'Visor', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Crear enterprise + user
        $enterprise = EnterpriseModel::create([
            'name' => 'Test Enterprise',
            'ruc' => '1790000000001',
            'tradename' => 'Test',
            'matrix_name' => 'Matrix',
            'phone' => '0999999999',
            'corporate_email' => 'test@test.com',
            'db_name' => '1790000000001',
        ]);

        $user = UserModel::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('Password123!'),
        ]);

        $user->roles()->attach(1);
        $user->enterprises()->attach($enterprise->id);

        // Crear DB tenant y migrar
        $this->crearDbTenant('1790000000001');
        $this->migrarTenant('1790000000001');

        // Login real para obtener token con enterprise_id
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'enterprise_id' => $enterprise->id,
        ]);

        $this->token = $loginResponse->json('response.token');
        $this->iva15Id = \DB::connection('pgsql')->table('sri_iva_percentages')->where('code', 'IVA_15')->value('id');
    }

    protected function tearDown(): void
    {
        $this->dropTenantDb('1790000000001');
        parent::tearDown();
    }

    private function dropTenantDb(string $dbName): void
    {
        // Cerrar todas las conexiones a la DB tenant
        \DB::purge('tenant');
        \DB::disconnect('tenant');

        $config = config('database.connections.pgsql');
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new \PDO($dsn, $config['username'], $config['password']);
        // Forzar cierre de conexiones activas (PostgreSQL 9.2+)
        $pdo->exec("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '{$dbName}' AND pid <> pg_backend_pid()");
        $pdo->exec("DROP DATABASE IF EXISTS \"{$dbName}\"");
    }

    private function crearDbTenant(string $dbName): void
    {
        $config = config('database.connections.pgsql');
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new \PDO($dsn, $config['username'], $config['password']);
        $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
        $stmt->execute([$dbName]);
        if (!$stmt->fetch()) {
            $pdo->exec("CREATE DATABASE \"{$dbName}\"");
        }
    }

    private function migrarTenant(string $dbName): void
    {
        config(['database.connections.tenant.database' => $dbName]);
        \DB::purge('tenant');
        \DB::reconnect('tenant');
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);
    }

    public function test_crear_product_devuelve_201(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/products', [
                'name' => 'Product Test',
                'barcode' => '789456123',
                'auxiliary_code' => 'AUX001',
                'description' => 'Descripcion del product',
                'base_price' => 100.50,
                'taxes' => [$this->iva15Id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.name', 'Product Test')
            ->assertJsonPath('response.barcode', '789456123')
            ->assertJsonPath('response.base_price', 100.5);
    }

    public function test_crear_product_sin_nombre_devuelve_422(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/products', [
                'base_price' => 100,
            ]);

        $response->assertStatus(422);
    }

    public function test_listar_products_devuelve_paginado(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->withToken($this->token)->postJson('/api/products', [
                'name' => "Product {$i}",
                'base_price' => $i * 10,
            ]);
        }

        $response = $this->withToken($this->token)
            ->getJson('/api/products?page=1&perPage=3');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.total', 5)
            ->assertJsonPath('response.page', 1)
            ->assertJsonPath('response.perPage', 3)
            ->assertJsonPath('response.lastPage', 2);
    }

    public function test_obtener_product_por_id(): void
    {
        $create = $this->withToken($this->token)
            ->postJson('/api/products', [
                'name' => 'Product Buscar',
                'base_price' => 50,
            ]);

        $id = $create->json('response.id');

        $response = $this->withToken($this->token)
            ->getJson("/api/products/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.id', $id)
            ->assertJsonPath('response.name', 'Product Buscar');
    }

    public function test_obtener_product_inexistente_devuelve_404(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/products/9999');

        $response->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    public function test_actualizar_product(): void
    {
        $create = $this->withToken($this->token)
            ->postJson('/api/products', [
                'name' => 'Product Original',
                'base_price' => 100,
            ]);

        $id = $create->json('response.id');

        $response = $this->withToken($this->token)
            ->putJson("/api/products/{$id}", [
                'name' => 'Product Actualizado',
                'base_price' => 200,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.name', 'Product Actualizado')
            ->assertJsonPath('response.base_price', 200);
    }

    public function test_cambiar_estado_product(): void
    {
        $create = $this->withToken($this->token)
            ->postJson('/api/products', [
                'name' => 'Product Estado',
                'base_price' => 100,
            ]);

        $id = $create->json('response.id');

        $response = $this->withToken($this->token)
            ->patchJson("/api/products/{$id}/status", [
                'status' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('products', [
            'id' => $id,
            'status' => false,
        ], 'tenant');
    }

    public function test_listar_products_sin_auth_devuelve_401(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    public function test_listar_con_busqueda(): void
    {
        $this->withToken($this->token)->postJson('/api/products', [
            'name' => 'Laptop HP',
            'base_price' => 800,
        ]);

        $this->withToken($this->token)->postJson('/api/products', [
            'name' => 'Mouse Logitech',
            'base_price' => 25,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/products?search=Laptop');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.total', 1)
            ->assertJsonPath('response.data.0.name', 'Laptop HP');
    }
}
