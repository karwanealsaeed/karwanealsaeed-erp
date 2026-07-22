<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'visa_applications',
            function (Blueprint $table): void {
                $table->string(
                    'passport_alert_fingerprint',
                    64,
                )
                    ->nullable();

                $table->timestamp(
                    'passport_alert_sent_at',
                )
                    ->nullable()
                    ->after(
                        'passport_alert_fingerprint',
                    );
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'visa_applications',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'passport_alert_fingerprint',
                    'passport_alert_sent_at',
                ]);
            },
        );
    }
};
