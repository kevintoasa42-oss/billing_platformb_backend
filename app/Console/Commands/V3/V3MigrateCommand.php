<?php

namespace App\Console\Commands\V3;

final class V3MigrateCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate
                            {--pretend : Muestra SQL sin ejecutar cambios}
                            {--step : Registra cada migración en un lote separado}';

    protected $description = 'Ejecuta las migraciones exclusivas de V3 (auth, core, platform, fiscal, integration) en orden';

    public function handle(): int
    {
        $extra = [];

        if ($this->option('pretend')) {
            $extra['--pretend'] = true;
        }

        if ($this->option('step')) {
            $extra['--step'] = true;
        }

        return $this->runAcrossPaths('migrate', $extra);
    }
}
