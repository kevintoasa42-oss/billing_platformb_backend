<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('emission_point_sequences', function (Blueprint $table): void {
            $table->dropUnique('emission_point_sequences_emission_point_id_unique');
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->nullOnDelete();
            $table->unique(['emission_point_id', 'carrier_id'], 'emission_point_sequences_point_carrier_unique');
            $table->index('carrier_id');
        });

        DB::connection('tenant')->statement(
            'CREATE UNIQUE INDEX emission_point_sequences_point_without_carrier_unique '
            .'ON emission_point_sequences (emission_point_id) WHERE carrier_id IS NULL',
        );
    }

    public function down(): void
    {
        DB::connection('tenant')->statement(
            'DROP INDEX IF EXISTS emission_point_sequences_point_without_carrier_unique',
        );

        Schema::connection('tenant')->table('emission_point_sequences', function (Blueprint $table): void {
            $table->dropIndex(['carrier_id']);
            $table->dropUnique('emission_point_sequences_point_carrier_unique');
            $table->dropForeign(['carrier_id']);
            $table->dropColumn('carrier_id');
            $table->unique('emission_point_id');
        });
    }
};
