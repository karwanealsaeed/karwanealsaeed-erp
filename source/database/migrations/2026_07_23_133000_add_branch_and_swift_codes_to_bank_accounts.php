<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'bank_accounts',
            function (Blueprint $table): void {
                $table->string(
                    'branch_code',
                    50,
                )
                    ->nullable()
                    ->after('branch');

                $table->string(
                    'swift_code',
                    20,
                )
                    ->nullable()
                    ->after('branch_code');
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'bank_accounts',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'branch_code',
                    'swift_code',
                ]);
            },
        );
    }
};