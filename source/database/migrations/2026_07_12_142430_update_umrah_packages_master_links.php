<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umrah_packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('umrah_packages', 'umrah_group_flight_id')) {
                $table->foreignId('umrah_group_flight_id')
                    ->nullable()
                    ->after('group_code')
                    ->constrained('umrah_group_flights')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('umrah_packages', 'makkah_hotel_id')) {
                $table->foreignId('makkah_hotel_id')
                    ->nullable()
                    ->after('makkah_hotel')
                    ->constrained('umrah_hotels')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('umrah_packages', 'madina_hotel_id')) {
                $table->foreignId('madina_hotel_id')
                    ->nullable()
                    ->after('madina_hotel')
                    ->constrained('umrah_hotels')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('umrah_packages', 'umrah_transport_route_id')) {
                $table->foreignId('umrah_transport_route_id')
                    ->nullable()
                    ->after('transport')
                    ->constrained('umrah_transport_routes')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Data safe rakhne ke liye columns drop nahi kar rahe.
    }
};