<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();

        $table->string('name');
        $table->string('father_name')->nullable();

        $table->string('cnic', 20)->unique()->nullable();
        $table->string('passport_no')->unique()->nullable();

        $table->string('mobile', 20);
        $table->string('whatsapp', 20)->nullable();

        $table->string('email')->nullable();
        $table->date('date_of_birth')->nullable();

        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('country')->default('Pakistan');

        $table->enum('customer_type', ['Regular', 'VIP'])->default('Regular');
        $table->boolean('status')->default(true);

        $table->text('notes')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
