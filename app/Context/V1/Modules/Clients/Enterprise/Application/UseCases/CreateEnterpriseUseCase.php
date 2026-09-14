<?php

namespace App\Context\V1\Modules\Clients\Enterprise\Application\UseCases;

use App\Context\V1\Modules\Clients\Enterprise\Application\DTOs\EnterpriseDTO;
use App\Context\V1\Modules\Clients\Enterprise\Domain\Models\Enterprise;
use App\Context\V1\Modules\Clients\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateEnterpriseUseCase
{
    public function __construct(
        private EnterpriseRepositoryInterface $enterpriseRepository,
    ) {}

    /**
     * Crea una enterprise y su base de datos de tenant (nombrada por el RUC).
     *
     * @param  EnterpriseDTO  $dto
     * @return EnterpriseDTO
     */
    public function ejecutar(EnterpriseDTO $dto): EnterpriseDTO
    {
        $enterprise = new Enterprise(
            name: $dto->name,
            ruc: $dto->ruc,
            tradename: $dto->tradename,
            matrix_name: $dto->matrix_name,
            phone: $dto->phone,
            corporate_email: $dto->corporate_email,
            db_name: $dto->ruc,
        );

        // Persistir la enterprise en la DB central.
        $enterprise = $this->enterpriseRepository->crear($enterprise);

        // Crear la base de datos del tenant (nombre = RUC).
        $this->crearBaseDeDatosTenant($enterprise->db_name);

        // Ejecutar migraciones tenant en la nueva DB.
        $this->migrarTenant($enterprise->db_name);

        return EnterpriseDTO::fromArray($enterprise->id ? [
            'id' => $enterprise->id,
            'name' => $enterprise->name,
            'ruc' => $enterprise->ruc,
            'tradename' => $enterprise->tradename,
            'matrix_name' => $enterprise->matrix_name,
            'phone' => $enterprise->phone,
            'corporate_email' => $enterprise->corporate_email,
        ] : []);
    }

    /**
     * Crea la base de datos PostgreSQL del tenant.
     * Usa una conexion PDO directa (fuera del pool de Laravel) porque
     * CREATE DATABASE no puede ejecutarse dentro de un bloque de transaccion.
     *
     * @param  string  $dbName
     * @return void
     */
    private function crearBaseDeDatosTenant(string $dbName): void
    {
        // Sanitizar el nombre de la DB (solo alfanumericos y guion bajo).
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        // Obtener configuracion de la conexion pgsql.
        $config = config('database.connections.pgsql');
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

        // Conexion PDO directa fuera del pool de Laravel (no hereda transacciones).
        $pdo = new \PDO($dsn, $config['username'], $config['password']);

        // Verificar si la base de datos ya existe.
        $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
        $stmt->execute([$dbName]);

        if ($stmt->fetch() === false) {
            $pdo->exec("CREATE DATABASE \"{$dbName}\"");
        }
    }

    /**
     * Ejecuta las migraciones tenant en la nueva DB.
     *
     * @param  string  $dbName
     * @return void
     */
    private function migrarTenant(string $dbName): void
    {
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        // Configurar conexion tenant.
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Ejecutar las migraciones tenant centralizadas.
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);
    }
}
