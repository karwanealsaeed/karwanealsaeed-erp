<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umrah_hotels', function (Blueprint $table): void {
            $table->id();

            $table->string('city'); // Makkah / Madinah
            $table->string('hotel_name');
            $table->string('similar_text')->nullable(); // OR SIMILAR
            $table->string('distance')->nullable(); // 850 METER
            $table->string('location')->nullable(); // IBRAHIM KHALIL ROAD
            $table->string('meal')->nullable(); // Room Only / Breakfast
            $table->string('rating')->nullable(); // 3 Star / 4 Star / 5 Star
            $table->string('contact_no')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umrah_hotels');
    }
};