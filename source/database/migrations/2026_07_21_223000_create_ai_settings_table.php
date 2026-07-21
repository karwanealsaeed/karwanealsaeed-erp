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
        Schema::create(
            'ai_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'key',
                    50,
                )
                    ->default('default')
                    ->unique();

                $table->string(
                    'provider',
                    50,
                )
                    ->default('local')
                    ->index();

                $table->string(
                    'model',
                    100,
                )->nullable();

                $table->boolean(
                    'external_enabled',
                )->default(false);

                $table->boolean(
                    'fallback_enabled',
                )->default(true);

                $table->unsignedSmallInteger(
                    'timeout_seconds',
                )->default(10);

                $table->unsignedInteger(
                    'max_output_characters',
                )->default(4000);

                $table->json(
                    'options',
                )->nullable();

                $table->foreignId(
                    'updated_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->timestamps();
            },
        );

        DB::table(
            'ai_settings',
        )->insert(
            [
                'key' =>
                    'default',

                'provider' =>
                    'local',

                'model' =>
                    null,

                'external_enabled' =>
                    false,

                'fallback_enabled' =>
                    true,

                'timeout_seconds' =>
                    10,

                'max_output_characters' =>
                    4000,

                'options' =>
                    null,

                'updated_by' =>
                    null,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ai_settings',
        );
    }
};
