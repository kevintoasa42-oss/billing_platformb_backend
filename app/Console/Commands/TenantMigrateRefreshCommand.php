<?php

namespace App\Console\Commands;

class TenantMigrateRefreshCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:refresh
                            {--enterprise= : ID de la empresa objetivo; exclusivo con --all}
                            {--all : Ejecuta la operación sobre todas las empresas}
                            {--step= : Número de migraciones a revertir y ejecutar}
                            {--seed : Ejecuta los seeders tenant después de migrar}
                            {--seeder= : Clase del seeder raíz}';

    protected $description = 'Revierte y ejecuta de nuevo migraciones de una o todas las empresas tenant';

    public function handle(): int
    {
        $options = $this->withTenantSeeder($this->tenantOptions());
        $step = $this->option('step');

        if ($step !== null && $step !== '') {
            $options['--step'] = $step;
        }

        return $this->runForEnterpriseOrAll('migrate:refresh', $options);
    }
}
