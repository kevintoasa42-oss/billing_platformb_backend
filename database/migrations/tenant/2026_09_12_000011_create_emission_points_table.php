<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('emission_points', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_office_id')->constrained('branch_offices')->cascadeOnDelete();
            $table->string('name');
            $table->string('emission_point', 20);
            $table->boolean('status')->default(false);
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_office_id', 'emission_point']);
            $table->index(['branch_office_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('emission_points');
    }
};
