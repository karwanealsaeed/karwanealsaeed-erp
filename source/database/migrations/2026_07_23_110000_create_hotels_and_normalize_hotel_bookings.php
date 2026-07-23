<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hotels')) {
            Schema::create('hotels', function (Blueprint $table): void {
                $table->id();
                $table
                    ->string('hotel_code', 32)
                    ->unique();
                $table->string('hotel_name');
                $table
                    ->foreignId('city_id')
                    ->constrained('cities')
                    ->restrictOnDelete();
                $table
                    ->foreignId('supplier_id')
                    ->nullable()
                    ->constrained('suppliers')
                    ->nullOnDelete();
                $table
                    ->unsignedTinyInteger('star_rating')
                    ->nullable();
                $table->text('address')->nullable();
                $table->string('contact_no', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('website')->nullable();
                $table
                    ->string('status', 50)
                    ->default('Active');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index([
                    'city_id',
                    'status',
                ]);
            });
        }

        Schema::table(
            'hotel_bookings',
            function (Blueprint $table): void {
                if (! Schema::hasColumn('hotel_bookings', 'hotel_id')) {
                    $table
                        ->foreignId('hotel_id')
                        ->nullable()
                        ->after('customer_id')
                        ->constrained('hotels')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('hotel_bookings', 'city_id')) {
                    $table
                        ->foreignId('city_id')
                        ->nullable()
                        ->after('hotel_id')
                        ->constrained('cities')
                        ->restrictOnDelete();
                }
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'hotel_bookings',
            function (Blueprint $table): void {
                if (Schema::hasColumn('hotel_bookings', 'city_id')) {
                    $table->dropConstrainedForeignId('city_id');
                }

                if (Schema::hasColumn('hotel_bookings', 'hotel_id')) {
                    $table->dropConstrainedForeignId('hotel_id');
                }
            },
        );

        Schema::dropIfExists('hotels');
    }
};