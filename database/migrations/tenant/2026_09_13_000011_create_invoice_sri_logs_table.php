<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_sri_logs table in each enterprise DB.
 * Stores all SRI responses (reception and authorization) tied to an invoice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_sri_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->string('access_key', 49);
            $table->string('operation_type', 20); // reception | authorization
            $table->boolean('status'); // true = success, false = error
            $table->string('sri_state', 30)->nullable(); // RECIBIDA | AUTORIZADO | RECHAZADO | DEVUELTA
            $table->text('response_message')->nullable(); // mensaje principal
            $table->json('raw_response')->nullable(); // respuesta completa del SRI
            $table->string('environment', 1)->nullable(); // 1=pruebas, 2=produccion
            $table->timestamp('authorization_date')->nullable(); // fecha de autorizacion del SRI
            $table->timestamps();

            $table->index('invoice_header_id');
            $table->index('access_key');
            $table->index('operation_type');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_sri_logs');
    }
};
