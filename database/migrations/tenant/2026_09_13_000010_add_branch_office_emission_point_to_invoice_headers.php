<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: add branch_office_id and emission_point_id foreign keys
 * to invoice_headers so the backend can resolve SRI codes and generate
 * unique sequentials atomically.
 *
 * The string columns establishment and emission_point remain as audit
 * snapshots of the SRI codes at the moment of emission.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('invoice_headers', function (Blueprint $table) {
            $table->foreignId('branch_office_id')
                ->nullable()
                ->after('carrier_id')
                ->constrained('branch_offices')
                ->nullOnDelete();

            $table->foreignId('emission_point_id')
                ->nullable()
                ->after('branch_office_id')
                ->constrained('emission_points')
                ->nullOnDelete();

            // Enforce uniqueness of (branch_office_id, emission_point_id, sequential)
            // at the database level to prevent duplicates even under concurrency.
            $table->unique(['branch_office_id', 'emission_point_id', 'sequential'], 'invoice_seq_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('invoice_headers', function (Blueprint $table) {
            $table->dropForeign(['emission_point_id']);
            $table->dropForeign(['branch_office_id']);
            $table->dropIndex('invoice_seq_unique');
            $table->dropColumn(['branch_office_id', 'emission_point_id']);
        });
    }
};
