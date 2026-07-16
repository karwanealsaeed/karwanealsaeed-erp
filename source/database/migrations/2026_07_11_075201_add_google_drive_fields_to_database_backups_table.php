<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('database_backups', function (Blueprint $table) {
            $table->string('drive_file_id')->nullable()->after('file_size');
            $table->string('drive_file_name')->nullable()->after('drive_file_id');
            $table->text('drive_web_view_link')->nullable()->after('drive_file_name');
            $table->string('drive_status')->default('Not Uploaded')->after('drive_web_view_link');
            $table->timestamp('drive_uploaded_at')->nullable()->after('drive_status');
            $table->text('drive_error')->nullable()->after('drive_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('database_backups', function (Blueprint $table) {
            $table->dropColumn([
                'drive_file_id',
                'drive_file_name',
                'drive_web_view_link',
                'drive_status',
                'drive_uploaded_at',
                'drive_error',
            ]);
        });
    }
};