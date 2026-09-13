<?php

namespace Database\Seeders\tenant;

use App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Models\BranchOfficeModel;
use Illuminate\Database\Seeder;

final class BranchOfficeSeeder extends Seeder
{
    public function run(): void
    {
        BranchOfficeModel::updateOrCreate(
            ['code_sri' => '001'],
            [
                'name' => 'Matriz',
                'status' => true,
                'type' => 'MATRIZ',
                'default' => true,
            ],
        );
    }
}
