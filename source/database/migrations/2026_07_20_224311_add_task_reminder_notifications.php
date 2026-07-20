<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('tasks')
            && ! Schema::hasColumn(
                'tasks',
                'reminder_sent_at',
            )
        ) {
            Schema::table(
                'tasks',
                function (Blueprint $table): void {
                    $table->timestamp(
                        'reminder_sent_at',
                    )
                        ->nullable()
                        ->after('reminder_at')
                        ->index();
                },
            );
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create(
                'notifications',
                function (Blueprint $table): void {
                    $table->uuid('id')->primary();

                    $table->string('type');

                    $table->morphs('notifiable');

                    $table->text('data');

                    $table->timestamp('read_at')
                        ->nullable();

                    $table->timestamps();
                },
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('tasks')
            && Schema::hasColumn(
                'tasks',
                'reminder_sent_at',
            )
        ) {
            Schema::table(
                'tasks',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'reminder_sent_at',
                    );
                },
            );
        }

        /*
         * The notifications table is intentionally retained
         * during rollback to protect notification history.
         */
    }
};
