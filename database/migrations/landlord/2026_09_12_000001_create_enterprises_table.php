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
            $table->string('name'); // Razon social
            $table->string('ruc', 13)->unique(); // RUC ecuatoriano
            $table->string('tradename'); // Nombre comercial
            $table->string('matrix_name'); // Nombre de la matriz
            $table->string('phone');
            $table->string('corporate_email');
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
