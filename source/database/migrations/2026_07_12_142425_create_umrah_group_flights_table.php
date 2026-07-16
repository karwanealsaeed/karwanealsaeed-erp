<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umrah_group_flights', function (Blueprint $table): void {
            $table->id();

            $table->string('group_code')->unique();
            $table->string('title')->nullable();

            $table->string('airline')->nullable();
            $table->string('airline_code')->nullable();

            $table->string('route')->nullable(); // LYP-JED-LYP
            $table->string('from_city')->nullable(); // FAISALABAD
            $table->string('to_city')->nullable(); // JEDDAH
            $table->string('return_city')->nullable(); // FAISALABAD

            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();

            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('duration_nights')->nullable();

            $table->unsignedInteger('total_flights')->default(2);
            $table->unsignedInteger('seats_total')->default(0);
            $table->unsignedInteger('seats_left')->default(0);

            $table->string('baggage')->nullable(); // 30+7 KG
            $table->string('meals')->nullable(); // INCLUDED

            $table->json('flight_segments')->nullable();

            $table->string('status')->default('Available');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umrah_group_flights');
    }
};