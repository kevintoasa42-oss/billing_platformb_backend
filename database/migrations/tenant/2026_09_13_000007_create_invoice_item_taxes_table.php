<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_item_taxes table.
 * Stores taxes per invoice item (detalles > detalle > impuestos > impuesto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_item_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->unsignedBigInteger('sri_iva_percentage_id')->nullable(); // reference to central catalog (snapshot)
            $table->string('code', 2); // 2=IVA
            $table->string('percentage_code', 2); // 0=0%, 4=15%, etc.
            $table->decimal('rate', 5, 2)->default(0); // rate (percentage)
            $table->decimal('tax_base', 14, 2)->default(0); // taxable base
            $table->decimal('tax', 14, 2)->default(0); // tax value
            $table->timestamps();

            $table->index('invoice_item_id');
            $table->index('sri_iva_percentage_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_item_taxes');
    }
};
