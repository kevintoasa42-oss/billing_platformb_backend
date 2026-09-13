<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_items table.
 * Stores line items (detalles) for each invoice.
 * Product data is a snapshot for audit purposes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // optional reference to original product
            $table->string('main_code'); // codigo principal
            $table->string('auxiliary_code')->nullable(); // codigo auxiliar
            $table->text('description'); // description
            $table->decimal('quantity', 14, 5)->default(0); // quantity
            $table->decimal('unit_price', 14, 5)->default(0); // precio unitario
            $table->decimal('discount', 14, 2)->default(0); // descuento
            $table->decimal('total_without_tax', 14, 2)->default(0); // precio total sin impuesto
            $table->timestamps();

            $table->index('invoice_header_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_items');
    }
};
