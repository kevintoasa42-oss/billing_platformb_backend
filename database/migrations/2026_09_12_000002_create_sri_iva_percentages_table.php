<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sri_iva_percentages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ej: IVA_15, IVA_0, EXENTO, NO_OBJETO
            $table->string('name'); // Ej: Tarifa general
            $table->decimal('percentage', 5, 2)->nullable(); // 15.00, 8.00, 5.00, 0.00; NULL para exento/no objeto
            $table->text('description')->nullable(); // Base legal / referencia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sri_iva_percentages');
    }
};
