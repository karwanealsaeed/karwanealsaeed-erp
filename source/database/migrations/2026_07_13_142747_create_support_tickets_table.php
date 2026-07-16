<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_no')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('service_type')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('priority')->default('Normal'); // Low, Normal, High, Urgent
            $table->string('status')->default('Open'); // Open, In Progress, Waiting Customer, Closed
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};