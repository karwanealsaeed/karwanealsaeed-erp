<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umrah_hotels', function (Blueprint $table): void {
            if (! Schema::hasColumn('umrah_hotels', 'has_similar')) {
                $table->boolean('has_similar')->default(false)->after('hotel_name');
            }
        });
    }

    public function down(): void
    {
        // Data safe rakhne ke liye column drop nahi kar rahe.
    }
};