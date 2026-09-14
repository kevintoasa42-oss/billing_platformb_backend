<?php

namespace App\Console\Commands\V3;

final class V3MigrateResetCommand extends AbstractV3MigrationCommand
{
    protected $signature = 'v3:migrate:reset
                            {--pretend : Muestra SQL sin ejecutar cambios}';

    protected $description = 'Revierte todas las migraciones de V3';

    public function handle(): int
    {
        $options = $this->v3Options();

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        return $this->call('migrate:reset', $options);
    }
}
