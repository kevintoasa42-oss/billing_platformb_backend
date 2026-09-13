<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the signatures table in each enterprise DB.
 * Stores electronic signature info for carriers (SRI Ecuador).
 * The physical .p12 file is stored in public/{enterprise_ruc}/{carrier_ruc}/{file_name}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->string('file_name'); // nombre del archivo .p12
            $table->string('file_path'); // ruta relativa: {enterprise_ruc}/{carrier_ruc}/{file_name}
            $table->string('password'); // contraseña del .p12
            $table->date('expires_at'); // fecha de expiración de la firma
            $table->string('environment'); // 'produccion' o 'pruebas'
            $table->boolean('emission_type')->default(true); // true=normal, false=contingencia
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('carrier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('signatures');
    }
};
