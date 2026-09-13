<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the carriers table in each enterprise DB.
 * A carrier is a sub-company with fiscal information for SRI authorization.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 13);
            $table->string('placa', 20)->nullable(); // placa (license plate)
            $table->string('name'); // razón social
            $table->string('tradename')->nullable(); // nombre comercial
            $table->string('matrix_address')->nullable(); // dirección matriz
            $table->string('special_taxpayer')->nullable(); // contribuyente especial (resolución)
            $table->boolean('accounting_required')->default(false); // obligado a llevar contabilidad
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('ruc');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('carriers');
    }
};
