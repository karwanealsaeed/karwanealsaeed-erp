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
            'travel_insurance_policies',
            function (
                Blueprint $table,
            ): void {
                $table
                    ->string(
                        'expiry_alert_fingerprint',
                        64,
                    )
                    ->nullable()
                    ->after(
                        'notes',
                    );

                $table
                    ->timestamp(
                        'expiry_alert_sent_at',
                    )
                    ->nullable()
                    ->after(
                        'expiry_alert_fingerprint',
                    );

                $table->index(
                    [
                        'approval_status',
                        'status',
                        'policy_end_date',
                    ],
                    'travel_insurance_expiry_scan_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'travel_insurance_policies',
            function (
                Blueprint $table,
            ): void {
                $table->dropIndex(
                    'travel_insurance_expiry_scan_index',
                );

                $table->dropColumn(
                    [
                        'expiry_alert_fingerprint',
                        'expiry_alert_sent_at',
                    ],
                );
            },
        );
    }
};