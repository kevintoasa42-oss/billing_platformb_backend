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
            $table->string('codigo', 2); // 2=IVA
            $table->string('codigo_porcentaje', 2); // 0=0%, 4=15%, etc.
            $table->decimal('tarifa', 5, 2)->default(0); // percentage
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('valor', 14, 2)->default(0);
            $table->timestamps();

            $table->index('invoice_item_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_item_taxes');
    }
};
