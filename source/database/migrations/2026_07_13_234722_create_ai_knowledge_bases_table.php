<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_knowledge_bases', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable()->index();
            $table->text('keywords');
            $table->longText('answer_english')->nullable();
            $table->longText('answer_urdu')->nullable();
            $table->longText('answer_roman_urdu')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->string('status', 20)->default('Active')->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_bases');
    }
};