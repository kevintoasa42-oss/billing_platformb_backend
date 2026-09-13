<?php

namespace App\Console\Commands;

class TenantMigrateResetCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:reset
                            {--enterprise= : ID de la empresa objetivo; exclusivo con --all}
                            {--all : Ejecuta la operación sobre todas las empresas}
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte todas las migraciones de una o todas las empresas tenant';

    public function handle(): int
    {
        $options = $this->tenantOptions();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        return $this->runForEnterpriseOrAll('migrate:reset', $options);
    }
}
