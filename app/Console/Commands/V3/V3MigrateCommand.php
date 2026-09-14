<?php

namespace App\Console\Commands\V3;

final class V3MigrateCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate
                            {--pretend : Muestra SQL sin ejecutar cambios}
                            {--step : Registra cada migración en un lote separado}';

    protected $description = 'Ejecuta las migraciones exclusivas de V3 (auth, core, platform, fiscal, integration)';

    public function handle(): int
    {
        $options = $this->v3Options();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        if ($this->option('step')) {
            $options['--step'] = true;
        }

        return $this->call('migrate', $options);
    }
}
