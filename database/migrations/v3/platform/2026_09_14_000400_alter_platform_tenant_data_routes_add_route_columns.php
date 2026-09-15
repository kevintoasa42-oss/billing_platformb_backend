<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE platform.tenant_data_routes ADD COLUMN IF NOT EXISTS route_version bigint NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE platform.tenant_data_routes ADD COLUMN IF NOT EXISTS writer_epoch bigint NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE platform.tenant_data_routes DROP COLUMN IF EXISTS writer_epoch');
        DB::statement('ALTER TABLE platform.tenant_data_routes DROP COLUMN IF EXISTS route_version');
    }
};
