<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'system_error_incidents',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'fingerprint',
                    64,
                )->unique();

                $table->string(
                    'severity',
                    20,
                )->index();

                $table->string(
                    'environment',
                    50,
                )->nullable()->index();

                $table->string(
                    'exception_class',
                    255,
                )->nullable();

                $table->text(
                    'message',
                );

                $table->string(
                    'file',
                    500,
                )->nullable();

                $table->unsignedInteger(
                    'line',
                )->nullable();

                $table->string(
                    'route_name',
                    255,
                )->nullable();

                $table->string(
                    'request_method',
                    12,
                )->nullable();

                $table->text(
                    'request_path',
                )->nullable();

                $table->unsignedBigInteger(
                    'user_id',
                )->nullable()->index();

                $table->string(
                    'user_email',
                    255,
                )->nullable();

                $table->unsignedBigInteger(
                    'occurrences',
                )->default(1);

                $table->timestamp(
                    'first_seen_at',
                )->index();

                $table->timestamp(
                    'last_seen_at',
                )->index();

                $table->timestamp(
                    'last_notified_at',
                )->nullable();

                $table->timestamp(
                    'resolved_at',
                )->nullable()->index();

                $table->unsignedBigInteger(
                    'resolved_by',
                )->nullable();

                $table->text(
                    'resolution_note',
                )->nullable();

                $table->json(
                    'context',
                )->nullable();

                $table->longText(
                    'sample_trace',
                )->nullable();

                $table->timestamps();
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'system_error_incidents',
        );
    }
};