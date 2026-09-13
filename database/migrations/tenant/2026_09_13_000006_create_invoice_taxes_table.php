<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_taxes table.
 * Stores total taxes per invoice (totalConImpuestos > totalImpuesto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->string('code', 2); // 2=IVA
            $table->string('percentage_code', 2); // 0=0%, 4=15%, etc.
            $table->decimal('taxable_base', 14, 2)->default(0); // base imponible
            $table->decimal('value', 14, 2)->default(0); // value
            $table->timestamps();

            $table->index('invoice_header_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_taxes');
    }
};
