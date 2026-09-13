<?php

namespace Tests\Unit\Console;

use App\Console\Commands\LandlordMigrateCommand;
use App\Console\Commands\LandlordMigrateFreshCommand;
use App\Console\Commands\LandlordMigrateStatusCommand;
use App\Console\Commands\TenantMigrateCommand;
use App\Console\Commands\TenantMigrateFreshCommand;
use App\Console\Commands\TenantMigrateRefreshCommand;
use App\Console\Commands\TenantMigrateResetCommand;
use App\Console\Commands\TenantMigrateRollbackCommand;
use App\Console\Commands\TenantMigrateStatusCommand;
use Database\Seeders\landlord\LandlordDatabaseSeeder;
use Database\Seeders\tenant\TenantDatabaseSeeder;
use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestCase;

final class MigrationCommandsTest extends TestCase
{
    public function test_landlord_migrate_forwards_only_its_fixed_scope_and_default_seeder(): void
    {
        $command = new LandlordMigrateCommand;
        [$exitCode, $migration] = $this->runCommand(
            $command,
            'migrate',
            [
                '--pretend' => true,
                '--step' => true,
                '--seed' => true,
                '--graceful' => true,
                '--isolated' => true,
            ],
        );

        self::assertSame(LaravelCommand::SUCCESS, $exitCode);
        self::assertSame('pgsql', $migration->receivedOptions['database']);
        self::assertSame('database/migrations/landlord', $migration->receivedOptions['path']);
        self::assertTrue($migration->receivedOptions['force']);
        self::assertTrue($migration->receivedOptions['pretend']);
        self::assertTrue($migration->receivedOptions['step']);
        self::assertTrue($migration->receivedOptions['seed']);
        self::assertSame(LandlordDatabaseSeeder::class, $migration->receivedOptions['seeder']);
        self::assertTrue($migration->receivedOptions['graceful']);
        self::assertTrue($migration->receivedOptions['isolated']);
    }

    public function test_landlord_status_uses_its_fixed_scope_without_force(): void
    {
        [$exitCode, $status] = $this->runCommand(
            new LandlordMigrateStatusCommand,
            'migrate:status',
            ['--pending' => true],
        );

        self::assertSame(LaravelCommand::SUCCESS, $exitCode);
        self::assertSame('pgsql', $status->receivedOptions['database']);
        self::assertSame('database/migrations/landlord', $status->receivedOptions['path']);
        self::assertTrue($status->receivedOptions['pending']);
        self::assertFalse($status->receivedOptions['force']);
    }

    public function test_landlord_migrate_forwards_an_explicit_seeder(): void
    {
        [, $migration] = $this->runCommand(
            new LandlordMigrateCommand,
            'migrate',
            ['--seeder' => 'Database\\Seeders\\CustomLandlordSeeder'],
        );

        self::assertSame('Database\\Seeders\\CustomLandlordSeeder', $migration->receivedOptions['seeder']);
        self::assertFalse($migration->receivedOptions['seed']);
    }

    public function test_landlord_fresh_forwards_drop_options_with_its_default_seeder(): void
    {
        [$exitCode, $fresh] = $this->runCommand(
            new LandlordMigrateFreshCommand,
            'migrate:fresh',
            ['--drop-views' => true, '--drop-types' => true, '--step' => true, '--seed' => true],
        );

        self::assertSame(LaravelCommand::SUCCESS, $exitCode);
        self::assertSame('pgsql', $fresh->receivedOptions['database']);
        self::assertSame('database/migrations/landlord', $fresh->receivedOptions['path']);
        self::assertTrue($fresh->receivedOptions['drop-views']);
        self::assertTrue($fresh->receivedOptions['drop-types']);
        self::assertTrue($fresh->receivedOptions['step']);
        self::assertSame(LandlordDatabaseSeeder::class, $fresh->receivedOptions['seeder']);
    }

    public function test_tenant_migrate_forwards_only_tenant_scope_and_default_seeder(): void
    {
        $command = new class extends TenantMigrateCommand
        {
            protected function enterprises(): ?Collection
            {
                return collect([(object) ['id' => 7, 'name' => 'Acme', 'db_name' => 'acme_tenant']]);
            }

            protected function connectEnterprise(object $enterprise): bool
            {
                return true;
            }
        };

        [$exitCode, $migration] = $this->runCommand(
            $command,
            'migrate',
            [
                '--pretend' => true,
                '--step' => true,
                '--seed' => true,
                '--graceful' => true,
                '--isolated' => true,
            ],
        );

        self::assertSame(LaravelCommand::SUCCESS, $exitCode);
        self::assertSame('tenant', $migration->receivedOptions['database']);
        self::assertSame('database/migrations/tenant', $migration->receivedOptions['path']);
        self::assertTrue($migration->receivedOptions['pretend']);
        self::assertTrue($migration->receivedOptions['step']);
        self::assertSame(TenantDatabaseSeeder::class, $migration->receivedOptions['seeder']);
        self::assertTrue($migration->receivedOptions['graceful']);
        self::assertTrue($migration->receivedOptions['isolated']);
    }

    public function test_legacy_tenant_fresh_requires_an_explicit_enterprise_or_all_target_before_any_database_access(): void
    {
        $command = new TenantMigrateCommand;
        $output = new BufferedOutput;
        $command->setLaravel($this->app);

        $exitCode = $command->run(new ArrayInput(['--fresh' => true]), $output);

        self::assertSame(LaravelCommand::FAILURE, $exitCode);
        self::assertStringContainsString('--enterprise=<id> o --all', $output->fetch());
    }

    public function test_destructive_tenant_commands_require_an_explicit_enterprise_or_all_target(): void
    {
        foreach ([
            new TenantMigrateFreshCommand,
            new TenantMigrateRefreshCommand,
            new TenantMigrateResetCommand,
            new TenantMigrateRollbackCommand,
        ] as $command) {
            $output = new BufferedOutput;
            $command->setLaravel($this->app);
            $exitCode = $command->run(new ArrayInput([]), $output);

            self::assertSame(LaravelCommand::FAILURE, $exitCode);
            self::assertStringContainsString('--enterprise=<id> o --all', $output->fetch());
        }
    }

    public function test_tenant_fresh_all_runs_the_tenant_migration_for_every_enterprise(): void
    {
        $command = new class extends TenantMigrateFreshCommand
        {
            protected function enterprises(): ?Collection
            {
                return collect([
                    (object) ['id' => 7, 'name' => 'Acme', 'db_name' => 'acme_tenant'],
                    (object) ['id' => 8, 'name' => 'Globex', 'db_name' => 'globex_tenant'],
                ]);
            }

            protected function connectEnterprise(object $enterprise): bool
            {
                return true;
            }
        };

        [$exitCode, $migration] = $this->runCommand(
            $command,
            'migrate:fresh',
            ['--all' => true, '--seed' => true],
        );

        self::assertSame(LaravelCommand::SUCCESS, $exitCode);
        self::assertSame(2, $migration->calls);
        self::assertSame('tenant', $migration->receivedOptions['database']);
        self::assertSame('database/migrations/tenant', $migration->receivedOptions['path']);
        self::assertSame(TenantDatabaseSeeder::class, $migration->receivedOptions['seeder']);
    }

    public function test_destructive_tenant_commands_reject_enterprise_and_all_together_before_database_access(): void
    {
        $command = new TenantMigrateFreshCommand;
        $output = new BufferedOutput;
        $command->setLaravel($this->app);

        $exitCode = $command->run(new ArrayInput(['--enterprise' => 7, '--all' => true]), $output);

        self::assertSame(LaravelCommand::FAILURE, $exitCode);
        self::assertStringContainsString('son excluyentes', $output->fetch());
    }

    public function test_wrappers_do_not_expose_connection_or_path_overrides(): void
    {
        foreach ([
            new LandlordMigrateCommand,
            new LandlordMigrateFreshCommand,
            new LandlordMigrateStatusCommand,
            new TenantMigrateCommand,
            new TenantMigrateFreshCommand,
            new TenantMigrateRefreshCommand,
            new TenantMigrateResetCommand,
            new TenantMigrateRollbackCommand,
            new TenantMigrateStatusCommand,
        ] as $command) {
            self::assertFalse($command->getDefinition()->hasOption('database'));
            self::assertFalse($command->getDefinition()->hasOption('path'));
            self::assertFalse($command->getDefinition()->hasOption('realpath'));
        }
    }

    public function test_destructive_tenant_commands_expose_the_all_option(): void
    {
        foreach ([
            new TenantMigrateFreshCommand,
            new TenantMigrateRefreshCommand,
            new TenantMigrateResetCommand,
            new TenantMigrateRollbackCommand,
        ] as $command) {
            self::assertTrue($command->getDefinition()->hasOption('all'));
        }
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{int, CapturingMigrationCommand}
     */
    private function runCommand(LaravelCommand $command, string $target, array $arguments): array
    {
        $application = new Application;
        $migration = new CapturingMigrationCommand($target);
        $application->addCommand($migration);
        $command->setLaravel($this->app);
        $command->setApplication($application);

        return [
            $command->run(new ArrayInput($arguments), new BufferedOutput),
            $migration,
        ];
    }
}

final class CapturingMigrationCommand extends Command
{
    /** @var array<string, mixed> */
    public array $receivedOptions = [];

    public int $calls = 0;

    public function __construct(string $name)
    {
        parent::__construct($name);

        foreach (['database', 'path', 'seeder', 'batch'] as $option) {
            $this->addOption($option, null, InputOption::VALUE_OPTIONAL);
        }

        foreach ([
            'force', 'pretend', 'step', 'seed', 'graceful', 'isolated', 'pending', 'drop-views', 'drop-types',
        ] as $option) {
            $this->addOption($option, null, InputOption::VALUE_NONE);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->calls++;
        $this->receivedOptions = $input->getOptions();

        return LaravelCommand::SUCCESS;
    }
}
