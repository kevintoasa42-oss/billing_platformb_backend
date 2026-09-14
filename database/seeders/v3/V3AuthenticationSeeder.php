<?php

namespace Database\Seeders\v3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Seeds a minimal, idempotent V3 master tenant and administrator. */
final class V3AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection('master_v3');

        $tenant = $database->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        if (! $tenant) {
            $database->table('platform.tenants')->insert([
                'id' => (string) Str::uuid(),
                'ruc' => '1790000000001',
                'name' => 'Empresa V3 Demo',
                'synthetic' => true,
            ]);
            $tenant = $database->table('platform.tenants')->where('ruc', '1790000000001')->first(['id']);
        }

        $user = $database->table('auth.users')->whereRaw('lower(email) = ?', ['admin@billing-v3.local'])->first(['id']);
        if (! $user) {
            $database->table('auth.users')->insert([
                'id' => (string) Str::uuid(),
                'email' => 'admin@billing-v3.local',
                'name' => 'Administrador V3',
                'first_name' => 'Administrador',
                'last_name' => 'V3',
                'password_hash' => Hash::make('Admin123!'),
                'platform_admin' => true,
                'active' => true,
            ]);
            $user = $database->table('auth.users')->whereRaw('lower(email) = ?', ['admin@billing-v3.local'])->first(['id']);
        }

        if (! $tenant || ! $user) {
            return;
        }

        $database->transaction(function () use ($database, $tenant, $user): void {
            $database->select("SELECT set_config('app.tenant_id', ?, true)", [(string) $tenant->id]);
            $membership = $database->table('auth.tenant_memberships')
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->first(['id']);

            if (! $membership) {
                $database->table('auth.tenant_memberships')->insert([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'id' => (string) Str::uuid(),
                    'active' => true,
                    'authorization_version' => 1,
                    'capabilities' => '{*}',
                ]);
            }
        });
    }
}
