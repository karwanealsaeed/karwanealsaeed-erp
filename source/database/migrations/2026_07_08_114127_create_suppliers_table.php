<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('company_name')->nullable();

            $table->string('mobile');
            $table->string('whatsapp')->nullable();

            $table->string('email')->nullable();

            $table->text('address')->nullable();

            $table->string('city')->nullable();
            $table->string('country')->default('Pakistan');

            $table->enum('supplier_type', [
                'Airline',
                'Hotel',
                'Visa',
                'Transport',
                'Other',
            ])->default('Other');

            $table->enum('status', [
                'Active',
                'Inactive',
            ])->default('Active');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};