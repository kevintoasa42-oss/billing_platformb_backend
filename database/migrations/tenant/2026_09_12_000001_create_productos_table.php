<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migracion TENANT: crea la tabla de productos en la DB de cada empresa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_barras', 100)->nullable();
            $table->string('codigo_auxiliar', 100)->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true); // true = activo, false = inactivo
            $table->decimal('precio_base', 12, 2)->default(0);
            $table->timestamps();

            $table->index('codigo_barras');
            $table->index('codigo_auxiliar');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('productos');
    }
};
