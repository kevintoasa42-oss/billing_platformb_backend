<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('identification_type', 50);
            $table->string('identification_number', 30);
            $table->string('name');
            $table->string('last_name');
            $table->string('status', 50);
            $table->string('address', 500);
            $table->string('phone', 30);
            $table->string('email');
            $table->string('type', 100);
            $table->string('plates', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['identification_type', 'identification_number']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('clients');
    }
};
