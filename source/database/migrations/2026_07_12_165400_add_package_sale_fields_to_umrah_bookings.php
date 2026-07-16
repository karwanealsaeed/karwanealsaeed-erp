<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umrah_bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('umrah_bookings', 'umrah_package_id')) {
                $table->foreignId('umrah_package_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('umrah_packages')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('umrah_bookings', 'package_price_type')) {
                $table->string('package_price_type')->nullable()->after('umrah_package_id');
            }

            if (! Schema::hasColumn('umrah_bookings', 'package_price_per_person')) {
                $table->decimal('package_price_per_person', 15, 2)->default(0)->after('package_price_type');
            }

            if (! Schema::hasColumn('umrah_bookings', 'seats_count')) {
                $table->unsignedInteger('seats_count')->default(1)->after('package_price_per_person');
            }
        });
    }

    public function down(): void
    {
        // Data safe rakhne ke liye columns drop nahi kar rahe.
    }
};