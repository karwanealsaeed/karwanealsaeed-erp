<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visa_applications')) {
            return;
        }

        Schema::table(
            'visa_applications',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'visa_applications',
                        'passport_expiry_date',
                    )
                ) {
                    $table->date(
                        'passport_expiry_date',
                    )
                        ->nullable()
                        ->after('passport_no')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'visa_applications',
                        'required_passport_validity_months',
                    )
                ) {
                    $table->unsignedTinyInteger(
                        'required_passport_validity_months',
                    )
                        ->default(6)
                        ->after('passport_expiry_date');
                }

                if (
                    ! Schema::hasColumn(
                        'visa_applications',
                        'passport_required_until',
                    )
                ) {
                    $table->date(
                        'passport_required_until',
                    )
                        ->nullable()
                        ->after(
                            'required_passport_validity_months',
                        )
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'visa_applications',
                        'passport_validity_status',
                    )
                ) {
                    $table->string(
                        'passport_validity_status',
                    )
                        ->nullable()
                        ->after('passport_required_until')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'visa_applications',
                        'passport_validity_checked_at',
                    )
                ) {
                    $table->timestamp(
                        'passport_validity_checked_at',
                    )
                        ->nullable()
                        ->after(
                            'passport_validity_status',
                        );
                }
            },
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('visa_applications')) {
            return;
        }

        $columns = [
            'passport_expiry_date',
            'required_passport_validity_months',
            'passport_required_until',
            'passport_validity_status',
            'passport_validity_checked_at',
        ];

        foreach ($columns as $column) {
            if (
                Schema::hasColumn(
                    'visa_applications',
                    $column,
                )
            ) {
                Schema::table(
                    'visa_applications',
                    function (
                        Blueprint $table,
                    ) use ($column): void {
                        $table->dropColumn($column);
                    },
                );
            }
        }
    }
};
