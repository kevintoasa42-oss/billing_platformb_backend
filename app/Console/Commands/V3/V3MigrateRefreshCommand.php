<?php

namespace App\Console\Commands\V3;

final class V3MigrateRefreshCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:refresh
                            {--step= : Número de lotes a revertir antes de re-migrar}';

    protected $description = 'Revierte y re-ejecuta las migraciones de V3';

    public function handle(): int
    {
        $extra = [];

        if ($this->option('step')) {
            $extra['--step'] = $this->option('step');
        }

        return $this->runAcrossPaths('migrate:refresh', $extra);
    }
}
