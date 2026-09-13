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
            // Existing counters are invoice counters, so they retain code 01.
            $table->string('document_code', 3)->default('01');
            $table->string('document_label', 191)->default('Factura');
            $table->dropUnique('emission_point_sequences_point_carrier_unique');
            $table->unique(
                ['emission_point_id', 'carrier_id', 'document_code'],
                'emission_point_sequences_point_carrier_document_unique',
            );
            $table->index('document_code', 'emission_point_sequences_document_code_index');
        });

        DB::connection('tenant')->statement(
            'DROP INDEX IF EXISTS emission_point_sequences_point_without_carrier_unique',
        );
        DB::connection('tenant')->statement(
            'CREATE UNIQUE INDEX emission_point_sequences_point_document_without_carrier_unique '
            .'ON emission_point_sequences (emission_point_id, document_code) WHERE carrier_id IS NULL',
        );
    }

    public function down(): void
    {
        DB::connection('tenant')->statement(
            'DROP INDEX IF EXISTS emission_point_sequences_point_document_without_carrier_unique',
        );

        Schema::connection('tenant')->table('emission_point_sequences', function (Blueprint $table): void {
            $table->dropIndex('emission_point_sequences_document_code_index');
            $table->dropUnique('emission_point_sequences_point_carrier_document_unique');
            $table->dropColumn(['document_code', 'document_label']);
            $table->unique(['emission_point_id', 'carrier_id'], 'emission_point_sequences_point_carrier_unique');
        });

        DB::connection('tenant')->statement(
            'CREATE UNIQUE INDEX emission_point_sequences_point_without_carrier_unique '
            .'ON emission_point_sequences (emission_point_id) WHERE carrier_id IS NULL',
        );
    }
};
