<?php

namespace Database\Seeders\tenant;

use App\Models\CarrierModel;
use Illuminate\Database\Seeder;

final class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['ruc' => '1790012345001', 'plate' => 'ABC-1001', 'name' => 'Transportes Andinos S.A.', 'tradename' => 'Andinos', 'matrix_address' => 'Quito, La Carolina', 'special_taxpayer' => '001-2026', 'accounting_required' => true, 'status' => true],
            ['ruc' => '1790012346001', 'plate' => 'ABC-1002', 'name' => 'Logística Sierra Cía. Ltda.', 'tradename' => 'Logística Sierra', 'matrix_address' => 'Quito, Iñaquito', 'special_taxpayer' => null, 'accounting_required' => true, 'status' => true],
            ['ruc' => '1790012347001', 'plate' => 'ABC-1003', 'name' => 'Carga Express del Pacífico S.A.', 'tradename' => 'Carga Express', 'matrix_address' => 'Guayaquil, Kennedy', 'special_taxpayer' => null, 'accounting_required' => false, 'status' => true],
            ['ruc' => '1790012348001', 'plate' => 'ABC-1004', 'name' => 'Distribuciones Austro S.A.S.', 'tradename' => 'Austro Distribuciones', 'matrix_address' => 'Cuenca, Totoracocha', 'special_taxpayer' => '004-2026', 'accounting_required' => true, 'status' => true],
            ['ruc' => '1790012349001', 'plate' => 'ABC-1005', 'name' => 'Servicios de Transporte Norte Cía. Ltda.', 'tradename' => 'Transporte Norte', 'matrix_address' => 'Ibarra, El Olivo', 'special_taxpayer' => null, 'accounting_required' => false, 'status' => true],
            ['ruc' => '1790012350001', 'plate' => 'ABC-1006', 'name' => 'Rutas del Sur S.A.', 'tradename' => 'Rutas del Sur', 'matrix_address' => 'Loja, El Valle', 'special_taxpayer' => null, 'accounting_required' => true, 'status' => true],
            ['ruc' => '1790012351001', 'plate' => 'ABC-1007', 'name' => 'Fletes Centro Cía. Ltda.', 'tradename' => 'Fletes Centro', 'matrix_address' => 'Ambato, Ficoa', 'special_taxpayer' => '007-2026', 'accounting_required' => true, 'status' => true],
            ['ruc' => '1790012352001', 'plate' => 'ABC-1008', 'name' => 'Movilidad Oriente S.A.S.', 'tradename' => 'Movilidad Oriente', 'matrix_address' => 'Puyo, Unión Base', 'special_taxpayer' => null, 'accounting_required' => false, 'status' => true],
            ['ruc' => '1790012353001', 'plate' => 'ABC-1009', 'name' => 'Transportes Insular S.A.', 'tradename' => 'Transportes Insular', 'matrix_address' => 'Puerto Ayora, Centro', 'special_taxpayer' => null, 'accounting_required' => true, 'status' => true],
            ['ruc' => '1790012354001', 'plate' => 'ABC-1010', 'name' => 'Carga y Envíos Ecuador Cía. Ltda.', 'tradename' => 'Carga Ecuador', 'matrix_address' => 'Santo Domingo, Centro', 'special_taxpayer' => '010-2026', 'accounting_required' => true, 'status' => true],
        ] as $carrier) {
            CarrierModel::updateOrCreate(['ruc' => $carrier['ruc']], $carrier);
        }
    }
}
