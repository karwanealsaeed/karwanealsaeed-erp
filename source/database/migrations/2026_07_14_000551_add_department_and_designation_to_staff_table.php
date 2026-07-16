<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff')) {
            return;
        }

        Schema::table('staff', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('departments')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('staff', 'designation_id')) {
                $table->foreignId('designation_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('designations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('staff')) {
            return;
        }

        Schema::table('staff', function (Blueprint $table): void {
            if (Schema::hasColumn('staff', 'designation_id')) {
                $table->dropConstrainedForeignId('designation_id');
            }

            if (Schema::hasColumn('staff', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
        });
    }
};