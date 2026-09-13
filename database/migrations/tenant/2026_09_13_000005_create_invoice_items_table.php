<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_items table.
 * Stores line items (detalles) for each invoice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->string('codigo_principal');
            $table->string('codigo_auxiliar')->nullable();
            $table->text('descripcion');
            $table->decimal('cantidad', 14, 5)->default(0);
            $table->decimal('precio_unitario', 14, 5)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('precio_total_sin_impuesto', 14, 2)->default(0);
            $table->timestamps();

            $table->index('invoice_header_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_items');
    }
};
