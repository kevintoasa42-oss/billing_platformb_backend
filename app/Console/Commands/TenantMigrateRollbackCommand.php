<?php

namespace App\Console\Commands;

final class TenantMigrateRollbackCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:rollback
                            {--enterprise= : ID de la empresa objetivo}
                            {--step= : Número de migraciones a revertir}
                            {--batch= : Lote de migración a revertir}
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte migraciones de una única empresa tenant';

    public function handle(): int
    {
        $options = $this->tenantOptions();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        foreach (['step', 'batch'] as $option) {
            $value = $this->option($option);

            if ($value !== null && $value !== '') {
                $options["--{$option}"] = $value;
            }
        }

        return $this->runForRequiredEnterprise('migrate:rollback', $options);
    }
}
