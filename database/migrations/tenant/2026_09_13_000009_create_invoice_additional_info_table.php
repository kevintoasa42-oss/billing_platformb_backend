<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant migration: creates the invoice_additional_info table.
 * Stores additional info per invoice (infoAdicional > campoAdicional).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('invoice_additional_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_header_id')->constrained('invoice_headers')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index('invoice_header_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('invoice_additional_info');
    }
};
