<?php

namespace App\Console\Commands;

final class LandlordMigrateResetCommand extends AbstractLandlordMigrationCommand
{
    protected $signature = 'landlord:migrate:reset
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte todas las migraciones exclusivamente de landlord';

    public function handle(): int
    {
        $options = $this->landlordOptions();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        return $this->call('migrate:reset', $options);
    }
}
