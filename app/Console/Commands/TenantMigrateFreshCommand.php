<?php

namespace App\Console\Commands;

class TenantMigrateFreshCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:fresh
                            {--enterprise= : ID de la empresa objetivo; exclusivo con --all}
                            {--all : Ejecuta la operación sobre todas las empresas}
                            {--drop-views : Elimina vistas además de tablas}
                            {--drop-types : Elimina tipos PostgreSQL además de tablas}
                            {--step : Registra cada migración en un lote separado}
                            {--seed : Ejecuta los seeders tenant después de migrar}
                            {--seeder= : Clase del seeder raíz}';

    protected $description = 'Elimina y recrea las tablas tenant de una empresa o de todas';

    public function handle(): int
    {
        $options = $this->withTenantSeeder($this->tenantOptions());

        foreach (['drop-views', 'drop-types', 'step'] as $option) {
            if ($this->option($option)) {
                $options["--{$option}"] = true;
            }
        }

        return $this->runForEnterpriseOrAll('migrate:fresh', $options);
    }
}
