<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central migration: creates the enterprise_signatures table in the enterprises DB.
 * Stores the electronic signature (.p12) for an enterprise (not a carrier).
 * The physical .p12 file is stored in public/{enterprise_ruc}/{file_name}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprise_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('password');
            $table->date('expires_at');
            $table->string('environment');
            $table->boolean('emission_type')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('enterprise_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_signatures');
    }
};
