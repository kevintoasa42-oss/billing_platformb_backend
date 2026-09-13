<?php

namespace App\Console\Commands;

use Database\Seeders\landlord\LandlordDatabaseSeeder;
use Illuminate\Console\Command;

abstract class AbstractLandlordMigrationCommand extends Command
{
    protected const CONNECTION = 'pgsql';

    protected const MIGRATION_PATH = 'database/migrations/landlord';

    /**
     * Build the fixed options shared by landlord migration commands.
     *
     * The connection and path deliberately never come from command input: a
     * landlord command must not be able to run tenant migrations (or vice versa).
     *
     * @return array<string, mixed>
     */
    protected function landlordOptions(bool $force = true): array
    {
        $options = [
            '--database' => self::CONNECTION,
            '--path' => self::MIGRATION_PATH,
        ];

        if ($force) {
            $options['--force'] = true;
        }

        return $options;
    }

    /**
     * Apply the selected seeder, using the landlord root seeder by default.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function withLandlordSeeder(array $options): array
    {
        $seeder = $this->option('seeder');

        if ($this->option('seed')) {
            $options['--seed'] = true;
        }

        if (is_string($seeder) && $seeder !== '') {
            $options['--seeder'] = $seeder;
        } elseif ($this->option('seed')) {
            $options['--seeder'] = LandlordDatabaseSeeder::class;
        }

        return $options;
    }
}
