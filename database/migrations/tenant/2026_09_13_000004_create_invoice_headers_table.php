<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_headers table.
 * Stores the main invoice data for SRI electronic invoicing (Ecuador).
 * carrier_id is nullable: null = enterprise issues directly,
 * not null = carrier issues (contrafactura).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->nullOnDelete();

            // infoTributaria
            $table->string('ambiente', 1)->default('1'); // 1=pruebas, 2=produccion
            $table->string('tipo_emision', 1)->default('1'); // 1=normal, 2=contingencia
            $table->string('ruc', 13); // issuer RUC
            $table->string('razon_social'); // issuer razon social
            $table->string('nombre_comercial')->nullable(); // issuer nombre comercial
            $table->string('clave_acceso', 49)->unique(); // 49 digits
            $table->string('cod_doc', 2)->default('01'); // 01=factura
            $table->string('estab', 3); // establecimiento
            $table->string('pto_emi', 3); // punto de emision
            $table->string('secuencial', 9); // secuencial
            $table->string('dir_matriz')->nullable(); // issuer direccion matriz

            // infoFactura
            $table->date('fecha_emision');
            $table->string('dir_establecimiento')->nullable();
            $table->string('obligado_contabilidad', 2)->default('NO'); // SI/NO
            $table->string('tipo_identificacion_comprador', 2); // 04=cedula, 05=RUC, 06=pasaporte, 07=consumidor final
            $table->string('razon_social_comprador');
            $table->string('identificacion_comprador');
            $table->string('direccion_comprador')->nullable();

            // Totales
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('propina', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('moneda', 10)->default('DOLAR');
            $table->string('placa', 20)->nullable(); // optional, for transport

            // Status (SRI authorization lifecycle)
            $table->string('status', 20)->default('PENDIENTE'); // PENDIENTE, RECHAZADO, AUTORIZADO

            $table->timestamps();

            $table->index('carrier_id');
            $table->index('clave_acceso');
            $table->index('status');
            $table->index(['estab', 'pto_emi', 'secuencial']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_headers');
    }
};
