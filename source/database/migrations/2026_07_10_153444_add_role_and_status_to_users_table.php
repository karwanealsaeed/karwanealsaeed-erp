<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'Admin',
                'Manager',
                'Accountant',
                'Sales Staff',
            ])->default('Admin')->after('email');

            $table->boolean('is_active')->default(true)->after('role');
            $table->string('mobile')->nullable()->after('is_active');
            $table->text('address')->nullable()->after('mobile');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'is_active',
                'mobile',
                'address',
            ]);
        });
    }
};