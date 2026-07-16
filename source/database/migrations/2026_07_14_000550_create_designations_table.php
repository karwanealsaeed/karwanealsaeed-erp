<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designations', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('name');
            $table->string('code', 30)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('Active')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['department_id', 'name'],
                'designation_department_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
};