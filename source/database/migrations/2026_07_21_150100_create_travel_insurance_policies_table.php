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
            'travel_insurance_policies',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'policy_no',
                )->unique();

                $table->foreignId(
                    'customer_id',
                )
                    ->constrained(
                        'customers',
                    )
                    ->restrictOnDelete();

                $table->foreignId(
                    'insurance_provider_id',
                )
                    ->nullable()
                    ->constrained(
                        'insurance_providers',
                    )
                    ->nullOnDelete();

                $table->foreignId(
                    'invoice_id',
                )
                    ->nullable()
                    ->constrained(
                        'invoices',
                    )
                    ->nullOnDelete();

                $table->string(
                    'traveller_name',
                );

                $table->string(
                    'passport_no',
                )->nullable();

                $table->date(
                    'date_of_birth',
                )->nullable();

                $table->string(
                    'beneficiary_name',
                )->nullable();

                $table->string(
                    'beneficiary_relationship',
                )->nullable();

                $table->string(
                    'destination_country',
                );

                $table->string(
                    'trip_type',
                )->default(
                    'Single Trip',
                );

                $table->string(
                    'coverage_type',
                )->default(
                    'Standard',
                );

                $table->decimal(
                    'coverage_amount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->char(
                    'coverage_currency',
                    3,
                )->default(
                    'USD',
                );

                $table->date(
                    'travel_start_date',
                );

                $table->date(
                    'travel_end_date',
                );

                $table->date(
                    'policy_start_date',
                )->nullable();

                $table->date(
                    'policy_end_date',
                )->nullable();

                $table->decimal(
                    'premium_amount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'supplier_cost',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'discount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'tax',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'total_amount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'paid_amount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'balance_amount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->decimal(
                    'profit_amount',
                    15,
                    2,
                )->default(
                    0,
                );

                $table->char(
                    'currency',
                    3,
                )->default(
                    'PKR',
                );

                $table->string(
                    'status',
                )->default(
                    'Draft',
                );

                $table->string(
                    'payment_status',
                )->default(
                    'Unpaid',
                );

                $table->string(
                    'approval_status',
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
                    'policy_document_name',
                )->nullable();

                $table->string(
                    'policy_document_path',
                )->nullable();

                $table->string(
                    'policy_document_mime',
                )->nullable();

                $table->unsignedBigInteger(
                    'policy_document_size',
                )->nullable();

                $table->text(
                    'notes',
                )->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'customer_id',
                        'status',
                    ],
                    'travel_insurance_customer_status_index',
                );

                $table->index(
                    [
                        'insurance_provider_id',
                        'status',
                    ],
                    'travel_insurance_provider_status_index',
                );

                $table->index(
                    [
                        'travel_start_date',
                        'travel_end_date',
                    ],
                    'travel_insurance_travel_dates_index',
                );

                $table->index(
                    [
                        'policy_end_date',
                        'status',
                    ],
                    'travel_insurance_expiry_status_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'travel_insurance_policies',
        );
    }
};