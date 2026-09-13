<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class LandlordMigrateCommand extends AbstractLandlordMigrationCommand
{
    protected $signature = 'landlord:migrate
                            {--fresh : Elimina y vuelve a crear las tablas landlord}
                            {--pretend : Muestra SQL sin ejecutar cambios}
                            {--step : Registra cada migración en un lote separado}
                            {--seed : Ejecuta los seeders landlord después de migrar}
                            {--seeder= : Clase del seeder raíz}
                            {--graceful : Devuelve éxito aunque Laravel encuentre un error}
                            {--isolated : Adquiere el bloqueo de migración de Laravel}';

    protected $description = 'Ejecuta únicamente las migraciones de la base central landlord';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            foreach (['pretend', 'graceful', 'isolated'] as $option) {
                if ($this->option($option)) {
                    $this->error("--{$option} no es compatible con landlord:migrate --fresh. Usa landlord:migrate:fresh.");

                    return Command::FAILURE;
                }
            }

            $options = $this->withLandlordSeeder($this->landlordOptions());

            if ($this->option('step')) {
                $options['--step'] = true;
            }

            return $this->call('migrate:fresh', $options);
        }

        $options = $this->landlordOptions();

        foreach (['pretend', 'step', 'graceful', 'isolated'] as $option) {
            if ($this->option($option)) {
                $options["--{$option}"] = true;
            }
        }

        return $this->call('migrate', $this->withLandlordSeeder($options));
    }
}
