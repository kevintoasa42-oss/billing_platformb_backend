<?php

namespace App\Console\Commands;

final class TenantMigrateResetCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:reset
                            {--enterprise= : ID de la empresa objetivo}
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte todas las migraciones de una única empresa tenant';

    public function handle(): int
    {
        $options = $this->tenantOptions();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        return $this->runForRequiredEnterprise('migrate:reset', $options);
    }
}
