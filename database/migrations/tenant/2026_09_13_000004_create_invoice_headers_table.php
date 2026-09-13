<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_headers table.
 * Stores the main invoice data for SRI electronic invoicing (Ecuador).
 * carrier_id is nullable: null = enterprise issues directly,
 * not null = carrier issues (contrafactura).
 * All data is a snapshot for audit purposes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->nullOnDelete();

            // infoTributaria (issuer snapshot)
            $table->string('environment', 1)->default('1'); // 1=pruebas, 2=produccion
            $table->string('emission_type', 1)->default('1'); // 1=normal, 2=contingencia
            $table->string('ruc', 13); // issuer RUC
            $table->string('legal_name'); // issuer razon social
            $table->string('tradename')->nullable(); // issuer nombre comercial
            $table->string('access_key', 49)->unique(); // 49 digits clave de acceso
            $table->string('document_code', 2)->default('01'); // 01=factura
            $table->string('establishment', 3); // establecimiento
            $table->string('emission_point', 3); // punto de emision
            $table->string('sequential', 9); // sequential number
            $table->string('matrix_address')->nullable(); // issuer direccion matriz

            // infoFactura (buyer snapshot + totals)
            $table->date('issue_date'); // fecha emision
            $table->string('establishment_address')->nullable(); // dir establecimiento
            $table->string('accounting_required', 2)->default('NO'); // SI/NO obligado contabilidad
            $table->string('buyer_identification_type', 2); // 04=cedula, 05=RUC, 06=pasaporte, 07=consumidor final
            $table->string('buyer_name'); // razon social comprador
            $table->string('buyer_identification'); // identificacion comprador
            $table->string('buyer_address')->nullable(); // direccion comprador
            $table->string('buyer_phone', 20)->nullable(); // telefono comprador
            $table->string('buyer_email')->nullable(); // email comprador

            // Totals
            $table->decimal('subtotal', 14, 2)->default(0); // sum of (quantity * unit_price) before discount
            $table->decimal('discount', 14, 2)->default(0); // total discount
            $table->decimal('tax_base', 14, 2)->default(0); // taxable base (subtotal - discount)
            $table->decimal('tax', 14, 2)->default(0); // total tax (IVA)
            $table->decimal('tip', 14, 2)->default(0); // tip
            $table->decimal('total', 14, 2)->default(0); // grand total
            $table->string('currency', 10)->default('DOLAR'); // currency
            $table->string('plate', 20)->nullable(); // optional, for transport

            // Status (SRI authorization lifecycle - in Spanish per requirement)
            $table->string('status', 20)->default('PENDIENTE'); // PENDIENTE, RECHAZADO, AUTORIZADO

            $table->timestamps();

            $table->index('carrier_id');
            $table->index('access_key');
            $table->index('status');
            $table->index(['establishment', 'emission_point', 'sequential']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_headers');
    }
};
