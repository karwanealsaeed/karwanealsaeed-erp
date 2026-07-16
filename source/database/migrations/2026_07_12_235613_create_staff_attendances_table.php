<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('Present'); // Present, Absent, Leave, Half Day
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('working_hours', 8, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['staff_member_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};