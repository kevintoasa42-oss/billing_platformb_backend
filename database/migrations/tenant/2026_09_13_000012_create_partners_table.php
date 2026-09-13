<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('partners', function (Blueprint $table): void {
            $table->id();
            $table->string('identification_type', 50);
            $table->string('identification_number', 30);
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('identification_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('partners');
    }
};
