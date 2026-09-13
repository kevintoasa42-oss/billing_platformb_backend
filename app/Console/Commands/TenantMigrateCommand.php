<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Ejecuta las migraciones de la carpeta database/migrations/tenant
 * en todas las bases de datos de enterprises existentes.
 */
class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate {--fresh : Drop all tables first} {--seed : Seed after migrate}';

    protected $description = 'Ejecuta migraciones tenant en todas las DBs de enterprises';

    public function handle(): int
    {
        $tenantPath = 'database/migrations/tenant';

        // Obtener todas las enterprises con su db_name.
        $enterprises = DB::connection('pgsql')->table('enterprises')->get(['id', 'name', 'db_name']);

        if ($enterprises->isEmpty()) {
            $this->warn('No hay enterprises registradas. No hay DBs tenant para migrar.');
            return Command::SUCCESS;
        }

        foreach ($enterprises as $enterprise) {
            $dbName = $enterprise->db_name;

            // Verificar que la DB existe.
            $exists = DB::connection('pgsql')
                ->select("SELECT 1 FROM pg_database WHERE datname = ?", [$dbName]);

            if (empty($exists)) {
                $this->warn("DB '{$dbName}' ({$enterprise->name}) no existe. Saltando...");
                continue;
            }

            $this->info("Migrando tenant: {$enterprise->name} ({$dbName})");

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
