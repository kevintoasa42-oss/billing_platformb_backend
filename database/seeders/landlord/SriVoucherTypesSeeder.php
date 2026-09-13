<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SriVoucherTypesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $startDate = '2000-01-01';

        $voucherTypes = [
            ['document' => 'Factura', 'code' => '01', 'sustentation_code' => '01', 'retention' => false],
            ['document' => 'Compra', 'code' => '03', 'sustentation_code' => '03', 'retention' => false],
            ['document' => 'Nota de Crédito', 'code' => '04', 'sustentation_code' => '04', 'retention' => false],
            ['document' => 'Nota de Débito', 'code' => '05', 'sustentation_code' => '05', 'retention' => false],
            ['document' => 'Guía de Remisión', 'code' => '06', 'sustentation_code' => '06', 'retention' => false],
            ['document' => 'Comprobante de Retención', 'code' => '07', 'sustentation_code' => '07', 'retention' => true],
        ];

        foreach ($voucherTypes as $voucherType) {
            DB::connection('pgsql')->table('sri_vouchers_types')->updateOrInsert(
                ['code' => $voucherType['code'], 'start_date' => $startDate],
                [...$voucherType, 'start_date' => $startDate, 'end_date' => null, 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }
}
