<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Database\Seeders;

use App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Models\BranchOfficeModel;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointSequenceModel;
use Illuminate\Database\Seeder;

final class EmissionPointSeeder extends Seeder
{
    public function run(): void
    {
        $branchOffice = BranchOfficeModel::where('code_sri', '001')->firstOrFail();
        $emissionPoint = EmissionPointModel::updateOrCreate(
            ['branch_office_id' => $branchOffice->id, 'emission_point' => '001'],
            [
                'name' => 'Punto de emisión principal',
                'status' => true,
                'default' => true,
            ],
        );

        EmissionPointSequenceModel::firstOrCreate(
            ['emission_point_id' => $emissionPoint->id],
            ['branch_office_id' => $branchOffice->id, 'next_sequential' => 1],
        );
    }
}
