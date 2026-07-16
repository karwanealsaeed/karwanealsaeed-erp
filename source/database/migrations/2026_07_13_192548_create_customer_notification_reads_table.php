<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_notification_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('customer_notification_id')->constrained('customer_notifications')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'customer_notification_id'], 'customer_notification_read_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notification_reads');
    }
};