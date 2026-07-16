<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            $table->string('company_name')->default('Karwan E Al Saeed Travel & Tours');
            $table->string('short_name')->nullable();
            $table->string('logo')->nullable();

            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->string('ntn_no')->nullable();
            $table->string('license_no')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account_title')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_iban')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};