<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql')->create('sri_vouchers_types', function (Blueprint $table): void {
            $table->id();
            $table->string('document', 191);
            $table->string('code', 3);
            $table->string('sustentation_code', 191)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('retention')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // The SRI may publish a new validity range for an existing code.
            $table->unique(['code', 'start_date'], 'sri_vouchers_types_code_start_date_unique');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('sri_vouchers_types');
    }
};
