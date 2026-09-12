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
            ['nombre' => 'admin', 'descripcion' => 'Administrador con acceso total al sistema.'],
            ['nombre' => 'gestor', 'descripcion' => 'Gestor con permisos de operacion.'],
            ['nombre' => 'visor', 'descripcion' => 'Visor con permisos de solo lectura.'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $rol['nombre']],
                array_merge($rol, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
