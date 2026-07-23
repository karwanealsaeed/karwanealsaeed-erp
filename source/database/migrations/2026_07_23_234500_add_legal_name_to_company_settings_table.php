<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasColumn(
                'company_settings',
                'legal_name',
            )
        ) {
            Schema::table(
                'company_settings',
                function (Blueprint $table): void {
                    $table
                        ->string('legal_name')
                        ->nullable()
                        ->after('company_name');
                },
            );
        }

        DB::table('company_settings')
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('legal_name')
                        ->orWhere(
                            'legal_name',
                            '',
                        );
                },
            )
            ->update([
                'legal_name' =>
                    DB::raw('company_name'),
            ]);
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'company_settings',
                'legal_name',
            )
        ) {
            Schema::table(
                'company_settings',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'legal_name',
                    );
                },
            );
        }
    }
};