<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'scheduler_heartbeats',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'monitor_key',
                )->unique();

                $table->timestamp(
                    'last_started_at',
                )->nullable();

                $table->timestamp(
                    'last_finished_at',
                )->nullable();

                $table->timestamp(
                    'last_success_at',
                )->nullable();

                $table->timestamp(
                    'last_failed_at',
                )->nullable();

                $table->unsignedInteger(
                    'last_duration_ms',
                )->nullable();

                $table->integer(
                    'last_exit_code',
                )->nullable();

                $table->unsignedInteger(
                    'consecutive_failures',
                )->default(0);

                $table->text(
                    'failure_message',
                )->nullable();

                $table->timestamp(
                    'last_alerted_at',
                )->nullable();

                $table->timestamps();
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'scheduler_heartbeats',
        );
    }
};