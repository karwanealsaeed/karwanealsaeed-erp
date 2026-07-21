<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'travel_insurance_payments',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'payment_no',
                )->unique();

                $table->foreignId(
                    'travel_insurance_policy_id',
                )
                    ->constrained(
                        'travel_insurance_policies',
                    )
                    ->cascadeOnDelete();

                $table->foreignId(
                    'account_voucher_id',
                )
                    ->nullable()
                    ->constrained(
                        'account_vouchers',
                    )
                    ->nullOnDelete();

                $table->date(
                    'payment_date',
                );

                $table->decimal(
                    'amount',
                    15,
                    2,
                );

                $table->string(
                    'payment_method',
                )->default(
                    'Cash',
                );

                $table->string(
                    'reference_no',
                )->nullable();

                $table->string(
                    'status',
                )->default(
                    'Pending',
                );

                $table->string(
                    'posting_status',
                )->default(
                    'Pending',
                );

                $table->foreignId(
                    'approved_by',
                )
                    ->nullable()
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->timestamp(
                    'approved_at',
                )->nullable();

                $table->string(
                    'receipt_name',
                )->nullable();

                $table->string(
                    'receipt_path',
                )->nullable();

                $table->string(
                    'receipt_mime',
                )->nullable();

                $table->unsignedBigInteger(
                    'receipt_size',
                )->nullable();

                $table->text(
                    'notes',
                )->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'travel_insurance_policy_id',
                        'status',
                    ],
                    'travel_insurance_payment_policy_status_index',
                );

                $table->index(
                    [
                        'payment_date',
                        'status',
                    ],
                    'travel_insurance_payment_date_status_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'travel_insurance_payments',
        );
    }
};