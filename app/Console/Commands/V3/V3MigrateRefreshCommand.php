<?php

namespace App\Console\Commands\V3;

final class V3MigrateRefreshCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:refresh
                            {--step= : Número de lotes a revertir antes de re-migrar}';

    protected $description = 'Revierte y re-ejecuta las migraciones de V3';

    public function handle(): int
    {
        $options = $this->v3Options();

        if ($this->option('step')) {
            $options['--step'] = $this->option('step');
        }

        return $this->call('migrate:refresh', $options);
    }
}
