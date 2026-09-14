<?php

namespace App\Console\Commands\V3;

final class V3MigrateStatusCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:status';

    protected $description = 'Muestra el estado de las migraciones de V3';

    public function handle(): int
    {
        return $this->runAcrossPaths('migrate:status', force: false);
    }
}
