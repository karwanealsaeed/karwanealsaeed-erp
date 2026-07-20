<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'database_backups',
            function (Blueprint $table): void {
                $table->string(
                    'backup_type',
                )->default('Manual');

                $table->string(
                    'checksum_sha256',
                    64,
                )->nullable();

                $table->string(
                    'verification_status',
                )->default('Pending');

                $table->timestamp(
                    'verified_at',
                )->nullable();

                $table->text(
                    'verification_error',
                )->nullable();
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'database_backups',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'backup_type',
                    'checksum_sha256',
                    'verification_status',
                    'verified_at',
                    'verification_error',
                ]);
            },
        );
    }
};