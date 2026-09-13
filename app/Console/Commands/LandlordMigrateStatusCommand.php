<?php

namespace App\Console\Commands;

final class LandlordMigrateStatusCommand extends AbstractLandlordMigrationCommand
{
    protected $signature = 'landlord:migrate:status
                            {--pending : Muestra únicamente las migraciones pendientes}';

    protected $description = 'Muestra el estado de las migraciones landlord';

    public function handle(): int
    {
        $options = $this->landlordOptions(force: false);

        if ($this->option('pending')) {
            $options['--pending'] = true;
        }

        return $this->call('migrate:status', $options);
    }
}
