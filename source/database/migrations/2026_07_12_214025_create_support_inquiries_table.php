<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('inquiry_no')->unique();
            $table->string('name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('service_type')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->text('smart_reply')->nullable();
            $table->string('status')->default('New');
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_inquiries');
    }
};