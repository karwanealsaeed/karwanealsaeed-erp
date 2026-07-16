<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table) {
    $table->id();

    $table->foreignId('country_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('name');          // <-- Add this
    $table->string('city');
    $table->string('iata_code', 3)->unique();
    $table->string('icao_code', 4)->nullable();
    $table->boolean('status')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};