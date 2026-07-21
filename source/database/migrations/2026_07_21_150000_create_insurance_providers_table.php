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
            'insurance_providers',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'supplier_id',
                )
                    ->nullable()
                    ->constrained(
                        'suppliers',
                    )
                    ->nullOnDelete();

                $table->string(
                    'name',
                );

                $table->string(
                    'company_name',
                )->nullable();

                $table->string(
                    'contact_person',
                )->nullable();

                $table->string(
                    'phone',
                )->nullable();

                $table->string(
                    'whatsapp',
                )->nullable();

                $table->string(
                    'email',
                )->nullable();

                $table->string(
                    'website',
                )->nullable();

                $table->text(
                    'address',
                )->nullable();

                $table->string(
                    'city',
                )->nullable();

                $table->string(
                    'country',
                )->default(
                    'Pakistan',
                );

                $table->string(
                    'license_no',
                )->nullable();

                $table->string(
                    'status',
                )->default(
                    'Active',
                )->index();

                $table->text(
                    'notes',
                )->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'name',
                        'status',
                    ],
                    'insurance_providers_name_status_index',
                );
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'insurance_providers',
        );
    }
};