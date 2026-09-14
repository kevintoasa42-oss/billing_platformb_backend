<?php

namespace App\Console\Commands\V3;

final class V3MigrateRollbackCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:rollback
                            {--pretend : Muestra SQL sin ejecutar cambios}
                            {--step= : Número de lotes a revertir}';

    protected $description = 'Revierte el último lote de migraciones de V3';

    public function handle(): int
    {
        $extra = [];

        if ($this->option('pretend')) {
            $extra['--pretend'] = true;
        }

        if ($this->option('step')) {
            $extra['--step'] = $this->option('step');
        }

        return $this->runAcrossPaths('migrate:rollback', $extra);
    }
}
