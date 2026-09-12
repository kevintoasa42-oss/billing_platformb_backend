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
        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Razon social
            $table->string('ruc', 13)->unique(); // RUC ecuatoriano
            $table->string('tradename'); // Nombre comercial
            $table->string('matrixname'); // Nombre de la matriz
            $table->string('telefono');
            $table->string('correo_corporativo');
            $table->string('db_name')->nullable(); // Nombre de la DB del tenant (= RUC)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprises');
    }
};
