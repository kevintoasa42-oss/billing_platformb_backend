<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO;
use App\Contexto\Enterprise\Dominio\Modelos\Empresa;
use App\Contexto\Enterprise\Dominio\Repositorios\EmpresaRepositoryInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CrearEmpresaCasoUso
{
    public function __construct(
        private EmpresaRepositoryInterface $empresaRepository,
    ) {}

    /**
     * Crea una empresa y su base de datos de tenant (nombrada por el RUC).
     *
     * @param  EmpresaDTO  $dto
     * @return EmpresaDTO
     */
    public function ejecutar(EmpresaDTO $dto): EmpresaDTO
    {
        $empresa = new Empresa(
            nombre: $dto->nombre,
            ruc: $dto->ruc,
            tradename: $dto->tradename,
            matrixname: $dto->matrixname,
            telefono: $dto->telefono,
            correo_corporativo: $dto->correo_corporativo,
            db_name: $dto->ruc,
        );

        // Persistir la empresa en la DB central.
        $empresa = $this->empresaRepository->crear($empresa);

        // Crear la base de datos del tenant (nombre = RUC).
        $this->crearBaseDeDatosTenant($empresa->db_name);

        // Ejecutar migraciones tenant en la nueva DB.
        $this->migrarTenant($empresa->db_name);

        return EmpresaDTO::fromArray($empresa->id ? [
            'id' => $empresa->id,
            'nombre' => $empresa->nombre,
            'ruc' => $empresa->ruc,
            'tradename' => $empresa->tradename,
            'matrixname' => $empresa->matrixname,
            'telefono' => $empresa->telefono,
            'correo_corporativo' => $empresa->correo_corporativo,
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

        // Ejecutar migraciones de la carpeta tenant.
        Artisan::call('migrate', [
            '--path' => database_path('migrations/tenant'),
            '--database' => 'tenant',
            '--force' => true,
        ]);
    }
}
