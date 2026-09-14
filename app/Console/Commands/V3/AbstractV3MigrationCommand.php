<?php

namespace App\Console\Commands\V3;

use Illuminate\Console\Command;

abstract class AbstractV3MigrationCommand extends Command
{
    protected const CONNECTION = 'master_v3';

    /**
     * Ordered migration paths under database/migrations/v3.
     *
     * Each context runs in its own migrate call so that dependencies between
     * schemas are respected (core must finish before fiscal/integration start,
     * even though they share the 000200 timestamp prefix).
     */
    protected const MIGRATION_PATHS = [
        'database/migrations/v3',
        'database/migrations/v3/core',
        'database/migrations/v3/platform',
        'database/migrations/v3/fiscal',
        'database/migrations/v3/integration',
    ];

    /**
     * Build the fixed options for a single V3 migration path.
     *
     * The connection deliberately never comes from command input: a V3 command
     * must not be able to run landlord/tenant migrations.
     *
     * @return array<string, mixed>
     */
    protected function v3Options(string $path, bool $force = true): array
    {
        $options = [
            '--database' => self::CONNECTION,
            '--path' => $path,
        ];

        if ($force) {
            $options['--force'] = true;
        }

        return $options;
    }

    /**
     * Run a migrate-family command across all V3 paths in order.
     *
     * @param  string  $command  Artisan command name (migrate, migrate:rollback, etc.)
     * @param  array<string, mixed>  $extraOptions  Additional options merged into every path call.
     * @param  bool  $force  Whether to pass --force (false for status commands).
     */
    protected function runAcrossPaths(string $command, array $extraOptions = [], bool $force = true): int
    {
        $exit = self::SUCCESS;

        foreach (self::MIGRATION_PATHS as $path) {
            $options = array_merge($this->v3Options($path, $force), $extraOptions);
            $exitCode = $this->call($command, $options);

            if ($exitCode !== self::SUCCESS) {
                return $exitCode;
            }
        }

        return $exit;
    }
}
