<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('session_token', 100)->index();
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('sender', 30);
            $table->string('language', 30)->default('Roman Urdu');
            $table->string('category')->nullable();
            $table->longText('message');
            $table->boolean('needs_human_support')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};