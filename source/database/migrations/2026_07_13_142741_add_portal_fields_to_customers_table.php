<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'portal_password')) {
                $table->string('portal_password')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('customers', 'portal_registered_at')) {
                $table->timestamp('portal_registered_at')->nullable()->after('portal_password');
            }

            if (! Schema::hasColumn('customers', 'last_portal_login_at')) {
                $table->timestamp('last_portal_login_at')->nullable()->after('portal_registered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn([
                'portal_password',
                'portal_registered_at',
                'last_portal_login_at',
            ]);
        });
    }
};