<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umrah_transport_routes', function (Blueprint $table): void {
            $table->id();

            $table->string('route_code')->nullable();
            $table->string('title');
            $table->string('transport_type')->nullable(); // Private / Sharing / Bus
            $table->string('vehicle_type')->nullable(); // Economy Bus / Hiace / Coaster
            $table->text('route_description')->nullable(); // JED-APT > MAK-H > MED-H > MAK-H > JED-APT
            $table->text('notes')->nullable();

            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umrah_transport_routes');
    }
};