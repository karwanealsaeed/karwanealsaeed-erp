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
            'visa_applications',
            function (
                Blueprint $table,
            ): void {
                $table
                    ->date(
                        'visa_issue_date',
                    )
                    ->nullable()
                    ->after(
                        'return_date',
                    );

                $table
                    ->date(
                        'visa_expiry_date',
                    )
                    ->nullable()
                    ->after(
                        'visa_issue_date',
                    );

                $table
                    ->string(
                        'visa_alert_fingerprint',
                        64,
                    )
                    ->nullable()
                    ->after(
                        'visa_expiry_date',
                    );

                $table
                    ->timestamp(
                        'visa_alert_sent_at',
                    )
                    ->nullable()
                    ->after(
                        'visa_alert_fingerprint',
                    );

                $table->index(
                    [
                        'status',
                        'visa_expiry_date',
                    ],
                    'visa_applications_visa_expiry_scan_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'visa_applications',
            function (
                Blueprint $table,
            ): void {
                $table->dropIndex(
                    'visa_applications_visa_expiry_scan_index',
                );

                $table->dropColumn(
                    [
                        'visa_issue_date',
                        'visa_expiry_date',
                        'visa_alert_fingerprint',
                        'visa_alert_sent_at',
                    ],
                );
            },
        );
    }
};
