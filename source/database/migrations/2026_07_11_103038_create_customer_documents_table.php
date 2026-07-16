<?php
use App\Http\Controllers\CustomerDocumentDownloadController;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Route::get('/customer-documents/{customerDocument}/download', CustomerDocumentDownloadController::class)
    ->name('customer-documents.download')
    ->middleware('auth');
    
        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->string('service_type')->default('General');
            $table->string('document_type');
            $table->string('title')->nullable();

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);

            $table->date('expiry_date')->nullable();
            $table->string('status')->default('Active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'service_type']);
            $table->index(['document_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_documents');
    }
};