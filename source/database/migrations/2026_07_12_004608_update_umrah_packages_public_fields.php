<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umrah_packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('umrah_packages', 'group_code')) {
                $table->string('group_code')->nullable()->after('package_no');
            }

            if (! Schema::hasColumn('umrah_packages', 'package_image')) {
                $table->string('package_image')->nullable()->after('subtitle');
            }

            if (! Schema::hasColumn('umrah_packages', 'departure_code')) {
                $table->string('departure_code')->nullable()->after('package_type');
            }

            if (! Schema::hasColumn('umrah_packages', 'arrival_code')) {
                $table->string('arrival_code')->nullable()->after('departure_code');
            }

            if (! Schema::hasColumn('umrah_packages', 'departure_at')) {
                $table->dateTime('departure_at')->nullable()->after('departure_city');
            }

            if (! Schema::hasColumn('umrah_packages', 'arrival_at')) {
                $table->dateTime('arrival_at')->nullable()->after('departure_at');
            }

            if (! Schema::hasColumn('umrah_packages', 'flight_route')) {
                $table->string('flight_route')->nullable()->after('airline');
            }

            if (! Schema::hasColumn('umrah_packages', 'duration_nights')) {
                $table->unsignedInteger('duration_nights')->nullable()->after('duration_days');
            }

            if (! Schema::hasColumn('umrah_packages', 'seats_available')) {
                $table->unsignedInteger('seats_available')->default(0)->after('flight_route');
            }

            if (! Schema::hasColumn('umrah_packages', 'seats_left')) {
                $table->unsignedInteger('seats_left')->default(0)->after('seats_available');
            }

            if (! Schema::hasColumn('umrah_packages', 'makkah_distance')) {
                $table->string('makkah_distance')->nullable()->after('makkah_hotel');
            }

            if (! Schema::hasColumn('umrah_packages', 'makkah_location')) {
                $table->string('makkah_location')->nullable()->after('makkah_distance');
            }

            if (! Schema::hasColumn('umrah_packages', 'madina_distance')) {
                $table->string('madina_distance')->nullable()->after('madina_hotel');
            }

            if (! Schema::hasColumn('umrah_packages', 'madina_location')) {
                $table->string('madina_location')->nullable()->after('madina_distance');
            }

            if (! Schema::hasColumn('umrah_packages', 'included_services')) {
                $table->json('included_services')->nullable()->after('exclusions');
            }

            if (! Schema::hasColumn('umrah_packages', 'other_included_service')) {
                $table->string('other_included_service')->nullable()->after('included_services');
            }

            if (! Schema::hasColumn('umrah_packages', 'gifted_services')) {
                $table->json('gifted_services')->nullable()->after('other_included_service');
            }

            if (! Schema::hasColumn('umrah_packages', 'sharing_price')) {
                $table->decimal('sharing_price', 15, 2)->nullable()->after('price');
            }

            if (! Schema::hasColumn('umrah_packages', 'quad_price')) {
                $table->decimal('quad_price', 15, 2)->nullable()->after('sharing_price');
            }

            if (! Schema::hasColumn('umrah_packages', 'triple_price')) {
                $table->decimal('triple_price', 15, 2)->nullable()->after('quad_price');
            }

            if (! Schema::hasColumn('umrah_packages', 'double_price')) {
                $table->decimal('double_price', 15, 2)->nullable()->after('triple_price');
            }
        });

        if (Schema::hasColumn('umrah_packages', 'status')) {
            DB::table('umrah_packages')
                ->where('status', 'Active')
                ->update(['status' => 'Available']);
        }

        if (Schema::hasColumn('umrah_packages', 'price') && Schema::hasColumn('umrah_packages', 'sharing_price')) {
            DB::table('umrah_packages')
                ->whereNull('sharing_price')
                ->update(['sharing_price' => DB::raw('price')]);
        }
    }

    public function down(): void
    {
        // Data safe rakhne ke liye columns drop nahi kar rahe.
    }
};