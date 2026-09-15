<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenants = DB::table('platform.tenants')->pluck('id');

        foreach ($tenants as $tenantId) {
            $exists = DB::table('platform.tenant_data_routes')
                ->where('tenant_id', $tenantId)
                ->exists();

            if (! $exists) {
                DB::table('platform.tenant_data_routes')->insert([
                    'tenant_id' => $tenantId,
                    'state' => 'consolidated',
                    'frozen' => false,
                    'route_version' => 1,
                    'writer_epoch' => 1,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Seed migration: no rollback needed
    }
};
