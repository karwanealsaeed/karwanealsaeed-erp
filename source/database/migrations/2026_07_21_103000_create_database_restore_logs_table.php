<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'database_restore_logs',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'database_backup_id',
                )
                    ->nullable()
                    ->constrained(
                        'database_backups',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'requested_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->string(
                    'source_backup_no',
                )->index();

                $table->string(
                    'safety_backup_no',
                )->nullable();

                $table->string(
                    'database_name',
                );

                $table->string(
                    'source_checksum',
                    64,
                )->nullable();

                $table->string(
                    'status',
                    30,
                )->default(
                    'Pending',
                )->index();

                $table->string(
                    'rollback_status',
                    30,
                )->nullable();

                $table->timestamp(
                    'started_at',
                )->nullable();

                $table->timestamp(
                    'completed_at',
                )->nullable();

                $table->timestamp(
                    'failed_at',
                )->nullable();

                $table->text(
                    'error_message',
                )->nullable();

                $table->json(
                    'context',
                )->nullable();

                $table->timestamps();
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'database_restore_logs',
        );
    }
};