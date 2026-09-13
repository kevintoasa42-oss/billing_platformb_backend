<?php

namespace App\Console\Commands;

class TenantMigrateRollbackCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:rollback
                            {--enterprise= : ID de la empresa objetivo; exclusivo con --all}
                            {--all : Ejecuta la operación sobre todas las empresas}
                            {--step= : Número de migraciones a revertir}
                            {--batch= : Lote de migración a revertir}
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte migraciones de una o todas las empresas tenant';

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

        return $this->runForEnterpriseOrAll('migrate:rollback', $options);
    }
}
