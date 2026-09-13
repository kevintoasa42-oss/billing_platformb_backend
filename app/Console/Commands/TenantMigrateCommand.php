<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Applies pending tenant migrations to all enterprises, or to one explicitly
 * selected enterprise. Destructive fresh runs require an explicit target:
 * one enterprise or all tenant databases.
 */
class TenantMigrateCommand extends AbstractTenantMigrationCommand
{
    protected $signature = 'tenant:migrate
                            {--enterprise= : Empresa objetivo; exclusivo con --all al usar --fresh}
                            {--all : Ejecuta --fresh sobre todas las empresas}
                            {--fresh : Elimina y vuelve a crear las tablas tenant}
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
            return $this->runFreshForEnterpriseOrAll();
        }

        $options = $this->withTenantSeeder($this->tenantOptions());

        foreach (['pretend', 'step', 'graceful', 'isolated'] as $option) {
            if ($this->option($option)) {
                $options["--{$option}"] = true;
            }
        }

        if ($this->option('all') && $this->hasEnterpriseOption()) {
            $this->error('Las opciones --enterprise=<id> y --all son excluyentes.');

            return Command::FAILURE;
        }

        if ($this->hasEnterpriseOption()) {
            $enterprise = $this->requiredEnterprise();

            return $enterprise === null
                ? Command::FAILURE
                : $this->runForEnterprise($enterprise, 'migrate', $options);
        }

        return $this->runForAllEnterprises('migrate', $options);
    }

    private function runFreshForEnterpriseOrAll(): int
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

        return $this->runForEnterpriseOrAll('migrate:fresh', $options);
    }
}
