<?php

namespace Tests\Feature\Product;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;
use Tests\TestCase;

class ProductoApiTest extends TestCase
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
            ['codigo' => 'IVA_15', 'nombre' => 'Tarifa general', 'porcentaje' => 15.00, 'descripcion' => 'IVA general', 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'IVA_0', 'nombre' => 'Tarifa cero', 'porcentaje' => 0.00, 'descripcion' => 'IVA 0%', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insertar roles
        \DB::connection('pgsql')->table('roles')->insert([
            ['nombre' => 'admin', 'descripcion' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'gestor', 'descripcion' => 'Gestor', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'visor', 'descripcion' => 'Visor', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Crear empresa + usuario
        $empresa = EmpresaModel::create([
            'nombre' => 'Test Empresa',
            'ruc' => '1790000000001',
            'tradename' => 'Test',
            'matrixname' => 'Matrix',
            'telefono' => '0999999999',
            'correo_corporativo' => 'test@test.com',
            'db_name' => '1790000000001',
        ]);

        $usuario = UsuarioModel::create([
            'nombre' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('Password123!'),
        ]);

        $usuario->roles()->attach(1);
        $usuario->empresas()->attach($empresa->id);

        // Crear DB tenant y migrar
        $this->crearDbTenant('1790000000001');
        $this->migrarTenant('1790000000001');

        // Login real para obtener token con enterprise_id
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'enterprise_id' => $empresa->id,
        ]);

        $this->token = $loginResponse->json('response.token');
        $this->iva15Id = \DB::connection('pgsql')->table('sri_iva_percentages')->where('codigo', 'IVA_15')->value('id');
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

    public function test_crear_producto_devuelve_201(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/productos', [
                'nombre' => 'Producto Test',
                'codigo_barras' => '789456123',
                'codigo_auxiliar' => 'AUX001',
                'descripcion' => 'Descripcion del producto',
                'precio_base' => 100.50,
                'impuestos' => [$this->iva15Id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.nombre', 'Producto Test')
            ->assertJsonPath('response.codigo_barras', '789456123')
            ->assertJsonPath('response.precio_base', 100.5);
    }

    public function test_crear_producto_sin_nombre_devuelve_422(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/productos', [
                'precio_base' => 100,
            ]);

        $response->assertStatus(422);
    }

    public function test_listar_productos_devuelve_paginado(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->withToken($this->token)->postJson('/api/productos', [
                'nombre' => "Producto {$i}",
                'precio_base' => $i * 10,
            ]);
        }

        $response = $this->withToken($this->token)
            ->getJson('/api/productos?page=1&perPage=3');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.total', 5)
            ->assertJsonPath('response.page', 1)
            ->assertJsonPath('response.perPage', 3)
            ->assertJsonPath('response.lastPage', 2);
    }

    public function test_obtener_producto_por_id(): void
    {
        $create = $this->withToken($this->token)
            ->postJson('/api/productos', [
                'nombre' => 'Producto Buscar',
                'precio_base' => 50,
            ]);

        $id = $create->json('response.id');

        $response = $this->withToken($this->token)
            ->getJson("/api/productos/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.id', $id)
            ->assertJsonPath('response.nombre', 'Producto Buscar');
    }

    public function test_obtener_producto_inexistente_devuelve_404(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/productos/9999');

        $response->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    public function test_actualizar_producto(): void
    {
        $create = $this->withToken($this->token)
            ->postJson('/api/productos', [
                'nombre' => 'Producto Original',
                'precio_base' => 100,
            ]);

        $id = $create->json('response.id');

        $response = $this->withToken($this->token)
            ->putJson("/api/productos/{$id}", [
                'nombre' => 'Producto Actualizado',
                'precio_base' => 200,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.nombre', 'Producto Actualizado')
            ->assertJsonPath('response.precio_base', 200);
    }

    public function test_cambiar_estado_producto(): void
    {
        $create = $this->withToken($this->token)
            ->postJson('/api/productos', [
                'nombre' => 'Producto Estado',
                'precio_base' => 100,
            ]);

        $id = $create->json('response.id');

        $response = $this->withToken($this->token)
            ->patchJson("/api/productos/{$id}/estado", [
                'estado' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('productos', [
            'id' => $id,
            'estado' => false,
        ], 'tenant');
    }

    public function test_listar_productos_sin_auth_devuelve_401(): void
    {
        $response = $this->getJson('/api/productos');

        $response->assertStatus(401);
    }

    public function test_listar_con_busqueda(): void
    {
        $this->withToken($this->token)->postJson('/api/productos', [
            'nombre' => 'Laptop HP',
            'precio_base' => 800,
        ]);

        $this->withToken($this->token)->postJson('/api/productos', [
            'nombre' => 'Mouse Logitech',
            'precio_base' => 25,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/productos?search=Laptop');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('response.total', 1)
            ->assertJsonPath('response.data.0.nombre', 'Laptop HP');
    }
}
