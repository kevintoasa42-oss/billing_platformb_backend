<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('branch_offices', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code_sri', 20)->unique();
            $table->boolean('status')->default(false);
            $table->string('type', 100);
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'default']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('branch_offices');
    }
};
