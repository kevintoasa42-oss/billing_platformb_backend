<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Applies pending tenant migrations to all enterprises, or to one explicitly
 * selected enterprise. The legacy --fresh option is intentionally restricted
 * to one enterprise because it destroys tenant data.
 */
class TenantMigrateCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate
                            {--enterprise= : Empresa objetivo; obligatoria al usar --fresh}
                            {--fresh : Elimina y vuelve a crear las tablas de una sola empresa}
                            {--pretend : Muestra SQL sin ejecutar cambios}
                            {--step : Registra cada migración en un lote separado}
                            {--seed : Ejecuta los seeders tenant después de migrar}
                            {--seeder= : Clase del seeder raíz}
                            {--graceful : Devuelve éxito aunque Laravel encuentre un error}
                            {--isolated : Adquiere el bloqueo de migración de Laravel}';

    protected $description = 'Ejecuta las migraciones tenant pendientes sin cruzar la ruta landlord';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            return $this->runFreshForRequiredEnterprise();
        }

        $options = $this->withTenantSeeder($this->tenantOptions());

        foreach (['pretend', 'step', 'graceful', 'isolated'] as $option) {
            if ($this->option($option)) {
                $options["--{$option}"] = true;
            }
        }

        $enterpriseId = $this->option('enterprise');

        if ($enterpriseId !== null && $enterpriseId !== '') {
            $enterprise = $this->requiredEnterprise();

            return $enterprise === null
                ? Command::FAILURE
                : $this->runForEnterprise($enterprise, 'migrate', $options);
        }

        $enterprises = $this->enterprises();

        if ($enterprises === null) {
            return Command::FAILURE;
        }

        if ($enterprises->isEmpty()) {
            $this->warn('No hay empresas registradas. No hay bases tenant para migrar.');

            return Command::SUCCESS;
        }

        $hasFailures = false;

        foreach ($enterprises as $enterprise) {
            $status = $this->runForEnterprise($enterprise, 'migrate', $options);
            $hasFailures = $hasFailures || $status !== Command::SUCCESS;
        }

        return $hasFailures ? Command::FAILURE : Command::SUCCESS;
    }

    private function runFreshForRequiredEnterprise(): int
    {
        foreach (['pretend', 'graceful', 'isolated'] as $option) {
            if ($this->option($option)) {
                $this->error("--{$option} no es compatible con tenant:migrate --fresh. Usa tenant:migrate:fresh.");

                return Command::FAILURE;
            }
        }

        $options = $this->withTenantSeeder($this->tenantOptions());

        if ($this->option('step')) {
            $options['--step'] = true;
        }

        return $this->runForRequiredEnterprise('migrate:fresh', $options);
    }
}
