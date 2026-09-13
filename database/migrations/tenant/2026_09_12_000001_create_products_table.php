<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migracion TENANT: crea la tabla de products en la DB de cada enterprise.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 100)->nullable();
            $table->string('auxiliary_code', 100)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true); // true = activo, false = inactivo
            $table->decimal('base_price', 12, 2)->default(0);
            $table->timestamps();

            $table->index('barcode');
            $table->index('auxiliary_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('products');
    }
};
