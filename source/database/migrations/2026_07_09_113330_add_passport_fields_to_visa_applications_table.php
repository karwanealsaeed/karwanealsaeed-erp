<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {

            $table->date('passport_received_date')->nullable()->after('passport_no');

            $table->date('passport_return_date')->nullable()->after('passport_received_date');

            $table->enum('passport_status', [
                'Pending',
                'Received',
                'Embassy',
                'Ready',
                'Returned',
            ])->default('Pending')->after('passport_return_date');

        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {

            $table->dropColumn([
                'passport_received_date',
                'passport_return_date',
                'passport_status',
            ]);

        });
    }
};