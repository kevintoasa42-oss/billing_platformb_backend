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
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->unique(['emission_point_id', 'partner_id'], 'emission_point_sequences_point_partner_unique');
            $table->index('partner_id');
        });

        DB::connection('tenant')->statement(
            'CREATE UNIQUE INDEX emission_point_sequences_point_without_partner_unique '
            .'ON emission_point_sequences (emission_point_id) WHERE partner_id IS NULL',
        );
    }

    public function down(): void
    {
        DB::connection('tenant')->statement(
            'DROP INDEX IF EXISTS emission_point_sequences_point_without_partner_unique',
        );

        Schema::connection('tenant')->table('emission_point_sequences', function (Blueprint $table): void {
            $table->dropIndex(['partner_id']);
            $table->dropUnique('emission_point_sequences_point_partner_unique');
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
            $table->unique('emission_point_id');
        });
    }
};
