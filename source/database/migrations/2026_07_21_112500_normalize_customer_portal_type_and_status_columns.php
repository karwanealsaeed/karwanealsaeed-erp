<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'customers',
            function (
                Blueprint $table,
            ): void {
                $table->string(
                    'customer_type',
                    50,
                )
                    ->default(
                        'Customer',
                    )
                    ->change();

                $table->string(
                    'status',
                    50,
                )
                    ->default(
                        'Active',
                    )
                    ->change();
            },
        );
    }

    public function down(): void
    {
        /*
         * Intentionally non-destructive.
         *
         * Purana enum/CHECK constraint restore karna portal values
         * aur existing customer records ko invalid bana sakta hai.
         */
    }
};