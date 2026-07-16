<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff')) {
            return;
        }

        Schema::create('staff', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('designation_id')
                ->nullable()
                ->constrained('designations')
                ->nullOnDelete();

            $table->string('staff_code', 50)->nullable()->unique();
            $table->string('employee_code', 50)->nullable()->unique();

            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('cnic', 50)->nullable()->index();
            $table->string('mobile', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('email')->nullable()->index();

            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('marital_status', 30)->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Pakistan');

            $table->date('joining_date')->nullable();
            $table->date('leaving_date')->nullable();
            $table->string('employment_type', 50)->nullable();

            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('salary', 15, 2)->default(0);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('deduction', 15, 2)->default(0);

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 50)->nullable();

            $table->string('photo')->nullable();
            $table->string('status', 30)->default('Active')->index();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['department_id', 'designation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};