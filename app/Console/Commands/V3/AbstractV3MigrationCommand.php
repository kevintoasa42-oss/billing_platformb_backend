<?php

namespace App\Console\Commands\V3;

use Illuminate\Console\Command;

abstract class AbstractV3MigrationCommand extends Command
{
    protected const CONNECTION = 'master_v3';

    /**
     * Subdirectories under database/migrations/v3 that hold V3 migrations.
     * The root directory holds the auth schema; the rest are organized by
     * bounded context (core, platform, fiscal, integration).
     */
    protected const MIGRATION_PATHS = [
        'database/migrations/v3',
        'database/migrations/v3/core',
        'database/migrations/v3/platform',
        'database/migrations/v3/fiscal',
        'database/migrations/v3/integration',
    ];

    /**
     * Build the fixed options shared by V3 migration commands.
     *
     * The connection and paths deliberately never come from command input: a
     * V3 command must not be able to run landlord/tenant migrations.
     *
     * @return array<string, mixed>
     */
    protected function v3Options(bool $force = true): array
    {
        $options = [
            '--database' => self::CONNECTION,
        ];

        foreach (self::MIGRATION_PATHS as $path) {
            $options['--path'][] = $path;
        }

        if ($force) {
            $options['--force'] = true;
        }

        return $options;
    }
}
