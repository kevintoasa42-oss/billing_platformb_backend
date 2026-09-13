<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_payments table.
 * Stores payment info per invoice (infoFactura > pagos > pago).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->string('forma_pago', 2); // 01=efectivo, 16=tarjeta debito, 19=tarjeta credito, 20=otros
            $table->decimal('total', 14, 2)->default(0);
            $table->integer('plazo')->default(0); // days
            $table->timestamps();

            $table->index('invoice_header_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_payments');
    }
};
