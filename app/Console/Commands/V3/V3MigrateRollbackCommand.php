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
        $options = $this->v3Options();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        if ($this->option('step')) {
            $options['--step'] = $this->option('step');
        }

        return $this->call('migrate:rollback', $options);
    }
}
