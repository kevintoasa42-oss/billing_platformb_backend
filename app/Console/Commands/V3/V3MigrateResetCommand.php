<?php

namespace App\Console\Commands\V3;

final class V3MigrateResetCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:reset
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte todas las migraciones de V3';

    public function handle(): int
    {
        $extra = [];

        if ($this->option('pretend')) {
            $extra['--pretend'] = true;
        }

        return $this->runAcrossPaths('migrate:reset', $extra);
    }
}
