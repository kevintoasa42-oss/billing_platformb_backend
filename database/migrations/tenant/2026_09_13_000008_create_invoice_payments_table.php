<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_payments table.
 * Stores payment info per invoice (infoFactura > pagos > pago).
 * References sri_payment_methods catalog (central DB) by ID.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->unsignedBigInteger('sri_payment_method_id')->nullable(); // reference to central catalog
            $table->string('payment_code', 2)->nullable(); // snapshot of SRI code (e.g. "01")
            $table->decimal('total', 14, 2)->default(0);
            $table->integer('term')->default(0); // term (days)
            $table->timestamps();

            $table->index('invoice_header_id');
            $table->index('sri_payment_method_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_payments');
    }
};
