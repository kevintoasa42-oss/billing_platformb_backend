<?php

namespace App\Console\Commands;

use Database\Seeders\tenant\TenantDatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class AbstractTenantMigrationCommand extends Command
{
    protected const LANDLORD_CONNECTION = 'pgsql';

    protected const TENANT_CONNECTION = 'tenant';

    protected const MIGRATION_PATH = 'database/migrations/tenant';

    /**
     * @return array<string, mixed>
     */
    protected function tenantOptions(bool $force = true): array
    {
        $options = [
            '--database' => self::TENANT_CONNECTION,
            '--path' => self::MIGRATION_PATH,
        ];

        if ($force) {
            $options['--force'] = true;
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function withTenantSeeder(array $options): array
    {
        $seeder = $this->option('seeder');

        if ($this->option('seed')) {
            $options['--seed'] = true;
        }

        if (is_string($seeder) && $seeder !== '') {
            $options['--seeder'] = $seeder;
        } elseif ($this->option('seed')) {
            $options['--seeder'] = TenantDatabaseSeeder::class;
        }

        return $options;
    }

    /**
     * Resolve the enterprise supplied to a command that changes tenant data.
     */
    protected function requiredEnterprise(): ?object
    {
        $enterpriseId = $this->option('enterprise');

        if ($enterpriseId === null || $enterpriseId === '') {
            $this->error('La opción --enterprise=<id> es obligatoria para esta operación.');

            return null;
        }

        try {
            $enterprise = DB::connection(self::LANDLORD_CONNECTION)
                ->table('enterprises')
                ->where('id', $enterpriseId)
                ->first(['id', 'name', 'db_name']);
        } catch (Throwable $exception) {
            $this->error("No se pudo consultar la empresa {$enterpriseId}: {$exception->getMessage()}");

            return null;
        }

        if ($enterprise === null) {
            $this->error("La empresa con id {$enterpriseId} no existe.");

            return null;
        }

        return $enterprise;
    }

    /**
     * @return Collection<int, object>|null
     */
    protected function enterprises(): ?Collection
    {
        try {
            return DB::connection(self::LANDLORD_CONNECTION)
                ->table('enterprises')
                ->orderBy('id')
                ->get(['id', 'name', 'db_name']);
        } catch (Throwable $exception) {
            $this->error("No se pudieron consultar las empresas: {$exception->getMessage()}");

            return null;
        }
    }

    /**
     * Configure and verify the connection for one enterprise.
     */
    protected function connectEnterprise(object $enterprise): bool
    {
        $dbName = $enterprise->db_name ?? null;

        if (! is_string($dbName) || trim($dbName) === '') {
            $this->error("La empresa {$this->enterpriseLabel($enterprise)} no tiene db_name configurado.");

            return false;
        }

        try {
            $databaseExists = DB::connection(self::LANDLORD_CONNECTION)
                ->select('SELECT 1 FROM pg_database WHERE datname = ?', [$dbName]);
        } catch (Throwable $exception) {
            $this->error("No se pudo verificar la base {$dbName}: {$exception->getMessage()}");

            return false;
        }

        if ($databaseExists === []) {
            $this->error("La base tenant '{$dbName}' de {$this->enterpriseLabel($enterprise)} no existe.");

            return false;
        }

        try {
            config(['database.connections.'.self::TENANT_CONNECTION.'.database' => $dbName]);
            DB::purge(self::TENANT_CONNECTION);
            DB::connection(self::TENANT_CONNECTION)->getPdo();
        } catch (Throwable $exception) {
            $this->error("No se pudo conectar a la base tenant '{$dbName}': {$exception->getMessage()}");

            return false;
        }

        return true;
    }

    /**
     * Run a Laravel migration subcommand after targeting and checking a tenant.
     *
     * @param  array<string, mixed>  $options
     */
    protected function runForEnterprise(object $enterprise, string $command, array $options): int
    {
        if (! $this->connectEnterprise($enterprise)) {
            return Command::FAILURE;
        }

        $this->info("Empresa: {$this->enterpriseLabel($enterprise)}");

        return $this->call($command, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function runForRequiredEnterprise(string $command, array $options): int
    {
        $enterprise = $this->requiredEnterprise();

        if ($enterprise === null) {
            return Command::FAILURE;
        }

        return $this->runForEnterprise($enterprise, $command, $options);
    }

    /**
     * Run a destructive command for one explicitly selected enterprise or for
     * all enterprises when the caller explicitly supplies --all.
     *
     * @param  array<string, mixed>  $options
     */
    protected function runForEnterpriseOrAll(string $command, array $options): int
    {
        $hasEnterprise = $this->hasEnterpriseOption();
        $all = (bool) $this->option('all');

        if ($hasEnterprise && $all) {
            $this->error('Las opciones --enterprise=<id> y --all son excluyentes.');

            return Command::FAILURE;
        }

        if (! $hasEnterprise && ! $all) {
            $this->error('Indica --enterprise=<id> o --all para esta operación.');

            return Command::FAILURE;
        }

        if ($hasEnterprise) {
            return $this->runForRequiredEnterprise($command, $options);
        }

        return $this->runForAllEnterprises($command, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function runForAllEnterprises(string $command, array $options): int
    {
        $enterprises = $this->enterprises();

        if ($enterprises === null) {
            return Command::FAILURE;
        }

        if ($enterprises->isEmpty()) {
            $this->warn('No hay empresas registradas. No hay bases tenant para procesar.');

            return Command::SUCCESS;
        }

        $hasFailures = false;

        foreach ($enterprises as $enterprise) {
            $status = $this->runForEnterprise($enterprise, $command, $options);
            $hasFailures = $hasFailures || $status !== Command::SUCCESS;
        }

        return $hasFailures ? Command::FAILURE : Command::SUCCESS;
    }

    protected function hasEnterpriseOption(): bool
    {
        $enterpriseId = $this->option('enterprise');

        return $enterpriseId !== null && $enterpriseId !== '';
    }

    protected function enterpriseLabel(object $enterprise): string
    {
        $name = is_string($enterprise->name ?? null) && $enterprise->name !== ''
            ? $enterprise->name
            : 'Sin nombre';

        return "{$name} (id: {$enterprise->id})";
    }
}
