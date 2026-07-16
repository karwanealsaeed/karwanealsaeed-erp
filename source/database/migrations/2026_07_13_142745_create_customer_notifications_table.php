<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('Notification'); // Notification, Offer, Promotion, Alert
            $table->string('service_type')->nullable();
            $table->text('message');
            $table->string('action_label')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->date('visible_from')->nullable();
            $table->date('visible_until')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->index(['customer_id', 'status', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notifications');
    }
};