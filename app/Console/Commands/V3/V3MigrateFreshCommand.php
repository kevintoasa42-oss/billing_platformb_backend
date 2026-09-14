<?php

namespace App\Console\Commands\V3;

final class V3MigrateFreshCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:fresh
                            {--drop-views : Elimina vistas además de tablas}
                            {--drop-types : Elimina tipos PostgreSQL además de tablas}
                            {--step : Registra cada migración en un lote separado}';

    protected $description = 'Elimina todas las tablas de V3 y re-ejecuta las migraciones desde cero';

    public function handle(): int
    {
        $extra = [];

        foreach (['drop-views', 'drop-types', 'step'] as $option) {
            if ($this->option($option)) {
                $extra["--{$option}"] = true;
            }
        }

        return $this->runAcrossPaths('migrate:fresh', $extra);
    }
}
