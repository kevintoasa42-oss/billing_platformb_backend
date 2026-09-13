<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migracion TENANT: crea la tabla pivote producto_impuesto.
 * Relaciona un producto del tenant con un tipo de IVA del SRI (central).
 * No hay FK a sri_iva_percentages porque esta en otra DB (central).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('producto_impuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            // Referencia logica a sri_iva_percentages en la DB central (sin FK fisica).
            $table->unsignedBigInteger('sri_iva_percentage_id');
            $table->timestamps();

            $table->unique(['producto_id', 'sri_iva_percentage_id']);
            $table->index('sri_iva_percentage_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('producto_impuesto');
    }
};
