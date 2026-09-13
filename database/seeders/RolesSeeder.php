<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Roles base del sistema.
     */
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'admin', 'description' => 'Administrador con acceso total al sistema.'],
            ['name' => 'gestor', 'description' => 'Gestor con permisos de operacion.'],
            ['name' => 'visor', 'description' => 'Visor con permisos de solo lectura.'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['name' => $rol['name']],
                array_merge($rol, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
