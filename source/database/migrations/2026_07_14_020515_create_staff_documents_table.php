<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_documents', function (Blueprint $table): void {
            $table->id();

            $table->string('document_no', 50)
                ->nullable()
                ->unique();

            $table->foreignId('staff_id')
                ->constrained('staff')
                ->cascadeOnDelete();

            $table->string('document_type', 100);
            $table->string('title');
            $table->string('document_number')->nullable();

            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable()->index();

            $table->string('issuing_authority')->nullable();

            $table->string('file_path')->nullable();

            $table->string('status', 30)
                ->default('Active')
                ->index();

            $table->text('notes')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['staff_id', 'document_type'],
                'staff_document_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
    }
};