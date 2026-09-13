<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central migration: creates the sri_payment_methods table.
 * Catalog of SRI payment methods (Tabla 24) for electronic invoicing.
 * Stored in the central DB (enterprises) — shared across all tenants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql')->create('sri_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique(); // 01, 15, 16, 17, 18, 19, 20, 21
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('sri_payment_methods');
    }
};
