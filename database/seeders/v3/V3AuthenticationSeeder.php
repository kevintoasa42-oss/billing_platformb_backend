<?php

namespace Database\Seeders\v3;

use App\Context\V3\Modules\Platform\Domain\Services\DefaultIamSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Seeds a minimal, idempotent V3 master tenant and administrator. */
final class V3AuthenticationSeeder extends Seeder
{
    /**
     * RUC of the default demo tenant. This is a real RUC registered with
     * the SRI Ecuador and is used consistently across all V3 seeders.
     */
    public const TENANT_RUC = '1752331700001';

    public function run(): void
    {
        $database = DB::connection('master_v3');

        $tenant = $database->table('platform.tenants')->where('ruc', self::TENANT_RUC)->first(['id']);
        if (! $tenant) {
            $database->table('platform.tenants')->insert([
                'id' => (string) Str::uuid(),
                'ruc' => self::TENANT_RUC,
                'name' => 'KEVIN XAVIER TOASA ANRANGO',
                'synthetic' => true,
            ]);
            $tenant = $database->table('platform.tenants')->where('ruc', self::TENANT_RUC)->first(['id']);
        }

        $user = $database->table('auth.users')->whereRaw('lower(email) = ?', ['kevintoasa43@gmail.com'])->first(['id']);
        if (! $user) {
            $database->table('auth.users')->insert([
                'id' => (string) Str::uuid(),
                'email' => 'kevintoasa43@gmail.com',
                'name' => 'KEVIN XAVIER TOASA ANRANGO',
                'first_name' => 'KEVIN XAVIER',
                'last_name' => 'TOASA ANRANGO',
                'password_hash' => Hash::make('Admin123!'),
                'platform_admin' => true,
                'active' => true,
            ]);
            $user = $database->table('auth.users')->whereRaw('lower(email) = ?', ['kevintoasa43@gmail.com'])->first(['id']);
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

            $rawIamSettings = $database->table('core.tenant_settings')->where('tenant_id', $tenant->id)->value('iam_settings');
            $iamSettings = is_array($rawIamSettings) ? $rawIamSettings : json_decode((string) $rawIamSettings, true);
            if (! is_array($iamSettings) || (array) ($iamSettings['menus'] ?? []) === []) {
                $database->table('core.tenant_settings')->updateOrInsert(
                    ['tenant_id' => $tenant->id],
                    ['iam_settings' => json_encode((new DefaultIamSettings)->value(), JSON_THROW_ON_ERROR), 'updated_at' => now()],
                );
            }
        });
    }
}
