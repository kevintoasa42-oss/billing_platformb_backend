<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class TenantMigrateStatusCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate:status
                            {--pending : Muestra únicamente las migraciones pendientes}';

    protected $description = 'Muestra el estado de migraciones de cada empresa tenant';

    public function handle(): int
    {
        $enterprises = $this->enterprises();

        if ($enterprises === null) {
            return Command::FAILURE;
        }

        if ($enterprises->isEmpty()) {
            $this->warn('No hay empresas registradas. No hay bases tenant para consultar.');

            return Command::SUCCESS;
        }

        $options = $this->tenantOptions(force: false);

        if ($this->option('pending')) {
            $options['--pending'] = true;
        }

        $hasFailures = false;

        foreach ($enterprises as $enterprise) {
            $this->newLine();
            $status = $this->runForEnterprise($enterprise, 'migrate:status', $options);
            $hasFailures = $hasFailures || $status !== Command::SUCCESS;
        }

        return $hasFailures ? Command::FAILURE : Command::SUCCESS;
    }
}
