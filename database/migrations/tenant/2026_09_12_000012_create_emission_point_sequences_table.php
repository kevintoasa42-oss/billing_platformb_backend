<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('emission_point_sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_office_id')->constrained('branch_offices')->cascadeOnDelete();
            $table->foreignId('emission_point_id')->constrained('emission_points')->cascadeOnDelete();
            $table->unsignedBigInteger('next_sequential')->default(1);
            $table->timestamps();

            $table->unique('emission_point_id');
            $table->index('branch_office_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('emission_point_sequences');
    }
};
