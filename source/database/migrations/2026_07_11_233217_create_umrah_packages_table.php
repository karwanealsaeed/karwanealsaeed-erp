<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umrah_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_no')->unique();
            $table->string('title');
            $table->string('slug')->unique()->nullable();
            $table->string('subtitle')->nullable();
            $table->string('package_type')->nullable();
            $table->string('departure_city')->nullable();
            $table->string('airline')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('makkah_nights')->nullable();
            $table->unsignedInteger('madina_nights')->nullable();
            $table->string('makkah_hotel')->nullable();
            $table->string('madina_hotel')->nullable();
            $table->string('transport')->nullable();
            $table->string('sharing_type')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('child_price', 15, 2)->nullable();
            $table->decimal('infant_price', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            $table->string('status')->default('Active');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umrah_packages');
    }
};