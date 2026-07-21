<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ai_usage_logs',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'request_uuid',
                )->unique();

                $table->string(
                    'session_token',
                    64,
                )
                    ->nullable()
                    ->index();

                $table->foreignId(
                    'customer_id',
                )
                    ->nullable()
                    ->constrained(
                        'customers',
                    )
                    ->nullOnDelete();

                $table->string(
                    'requested_provider',
                    50,
                )->default('local');

                $table->string(
                    'provider',
                    50,
                )->default('local');

                $table->string(
                    'model',
                    100,
                )->nullable();

                $table->string(
                    'source',
                    50,
                )->default('local_rules');

                $table->string(
                    'language',
                    30,
                )->nullable();

                $table->string(
                    'category',
                    100,
                )->nullable();

                $table->string(
                    'status',
                    20,
                )
                    ->default('success')
                    ->index();

                $table->boolean(
                    'fallback_used',
                )->default(false);

                $table->unsignedInteger(
                    'latency_ms',
                )->default(0);

                $table->unsignedInteger(
                    'input_characters',
                )->default(0);

                $table->unsignedInteger(
                    'output_characters',
                )->default(0);

                $table->string(
                    'error_code',
                    100,
                )->nullable();

                $table->text(
                    'error_message',
                )->nullable();

                $table->json(
                    'meta',
                )->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'provider',
                        'created_at',
                    ],
                );

                $table->index(
                    [
                        'source',
                        'created_at',
                    ],
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ai_usage_logs',
        );
    }
};
