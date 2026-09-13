<?php

namespace App\Console\Commands;

final class LandlordMigrateFreshCommand extends AbstractLandlordMigrationCommand
{
    protected $signature = 'landlord:migrate:fresh
                            {--drop-views : Elimina vistas además de tablas}
                            {--drop-types : Elimina tipos PostgreSQL además de tablas}
                            {--step : Registra cada migración en un lote separado}
                            {--seed : Ejecuta los seeders landlord después de migrar}
                            {--seeder= : Clase del seeder raíz}';

    protected $description = 'Elimina y recrea exclusivamente las tablas landlord';

    public function handle(): int
    {
        $options = $this->withLandlordSeeder($this->landlordOptions());

        foreach (['drop-views', 'drop-types', 'step'] as $option) {
            if ($this->option($option)) {
                $options["--{$option}"] = true;
            }
        }

        return $this->call('migrate:fresh', $options);
    }
}
