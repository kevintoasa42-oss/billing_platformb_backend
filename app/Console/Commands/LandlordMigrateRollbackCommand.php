<?php

namespace App\Console\Commands;

final class LandlordMigrateRollbackCommand extends AbstractLandlordMigrationCommand
{
    protected $signature = 'landlord:migrate:rollback
                            {--step= : Número de migraciones a revertir}
                            {--batch= : Lote de migración a revertir}
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte migraciones exclusivamente de landlord';

    public function handle(): int
    {
        $options = $this->landlordOptions();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        foreach (['step', 'batch'] as $option) {
            $value = $this->option($option);

            if ($value !== null && $value !== '') {
                $options["--{$option}"] = $value;
            }
        }

        return $this->call('migrate:rollback', $options);
    }
}
