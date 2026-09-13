<?php

namespace App\Console\Commands;

final class LandlordMigrateRefreshCommand extends AbstractLandlordMigrationCommand
{
    protected $signature = 'landlord:migrate:refresh
                            {--step= : Número de migraciones a revertir y ejecutar}
                            {--seed : Ejecuta los seeders landlord después de migrar}
                            {--seeder= : Clase del seeder raíz}';

    protected $description = 'Revierte y ejecuta de nuevo las migraciones landlord';

    public function handle(): int
    {
        $options = $this->withLandlordSeeder($this->landlordOptions());
        $step = $this->option('step');

        if ($step !== null && $step !== '') {
            $options['--step'] = $step;
        }

        return $this->call('migrate:refresh', $options);
    }
}
