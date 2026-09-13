<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Ejecuta las migraciones de la carpeta database/migrations/tenant
 * en todas las bases de datos de empresas existentes.
 */
class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate {--fresh : Drop all tables first} {--seed : Seed after migrate}';

    protected $description = 'Ejecuta migraciones tenant en todas las DBs de empresas';

    public function handle(): int
    {
        $tenantPath = 'database/migrations/tenant';

        // Obtener todas las empresas con su db_name.
        $empresas = DB::connection('pgsql')->table('enterprises')->get(['id', 'nombre', 'db_name']);

        if ($empresas->isEmpty()) {
            $this->warn('No hay empresas registradas. No hay DBs tenant para migrar.');
            return Command::SUCCESS;
        }

        foreach ($empresas as $empresa) {
            $dbName = $empresa->db_name;

            // Verificar que la DB existe.
            $exists = DB::connection('pgsql')
                ->select("SELECT 1 FROM pg_database WHERE datname = ?", [$dbName]);

            if (empty($exists)) {
                $this->warn("DB '{$dbName}' ({$empresa->nombre}) no existe. Saltando...");
                continue;
            }

            $this->info("Migrando tenant: {$empresa->nombre} ({$dbName})");

            // Configurar conexion tenant.
            config(['database.connections.tenant.database' => $dbName]);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Ejecutar migraciones.
            $options = ['--path' => $tenantPath, '--force' => true];

            if ($this->option('fresh')) {
                $this->call('migrate:fresh', array_merge($options, ['--database' => 'tenant']));
            } else {
                $this->call('migrate', array_merge($options, ['--database' => 'tenant']));
            }
        }

        $this->info('Migraciones tenant completadas.');
        return Command::SUCCESS;
    }
}
