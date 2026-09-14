<?php

namespace App\Console\Commands\V3;

use Illuminate\Console\Command;

final class V3SeedCommand extends Command
{
    protected $signature = 'v3:seed
                            {--class= : Clase seeder específica a ejecutar}
                            {--force : Forzar ejecución en producción}';

    protected $description = 'Ejecuta los seeders de V3 (auth, economic activities, company, establishments, vehicles, SRI IVA)';

    public function handle(): int
    {
        $options = [
            '--database' => 'master_v3',
            '--force' => true,
        ];

        $seeder = $this->option('class');

        if ($seeder) {
            $options['--class'] = $seeder;
        } else {
            $options['--class'] = 'Database\\Seeders\\v3\\MasterV3DatabaseSeeder';
        }

        return $this->call('db:seed', $options);
    }
}
