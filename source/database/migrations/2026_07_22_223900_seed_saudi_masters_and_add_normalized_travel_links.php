<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'countries',
                'airlines',
                'cities',
                'umrah_group_flights',
                'umrah_packages',
                'umrah_hotels',
                'travel_insurance_policies',
            ]
            as $table
        ) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException(
                    "Required table missing: {$table}",
                );
            }
        }

        foreach (
            [
                ['umrah_group_flights', 'airline_id'],
                ['umrah_packages', 'airline_id'],
                ['umrah_hotels', 'city_id'],
                [
                    'travel_insurance_policies',
                    'destination_country_id',
                ],
            ]
            as [$table, $column]
        ) {
            if (Schema::hasColumn($table, $column)) {
                throw new \RuntimeException(
                    "Unexpected existing column: {$table}.{$column}",
                );
            }
        }

        $saudiCountryId =
            $this->resolveOrCreateCountry();

        $this->resolveOrCreateAirline();

        $this->resolveOrCreateCity(
            $saudiCountryId,
            'Makkah',
            'SA-MAKKAH',
        );

        $this->resolveOrCreateCity(
            $saudiCountryId,
            'Madinah',
            'SA-MADINAH',
        );

        Schema::table(
            'umrah_group_flights',
            function (Blueprint $table): void {
                $table
                    ->foreignId('airline_id')
                    ->nullable()
                    ->after('title');
            },
        );

        Schema::table(
            'umrah_packages',
            function (Blueprint $table): void {
                $table
                    ->foreignId('airline_id')
                    ->nullable()
                    ->after('umrah_group_flight_id');
            },
        );

        Schema::table(
            'umrah_hotels',
            function (Blueprint $table): void {
                $table
                    ->foreignId('city_id')
                    ->nullable()
                    ->after('id');
            },
        );

        Schema::table(
            'travel_insurance_policies',
            function (Blueprint $table): void {
                $table
                    ->foreignId(
                        'destination_country_id',
                    )
                    ->nullable()
                    ->after(
                        'beneficiary_relationship',
                    );
            },
        );

        $this->backfillAirline(
            'umrah_group_flights',
            [
                'airline',
                'airline_code',
            ],
        );

        $this->backfillAirline(
            'umrah_packages',
            ['airline'],
        );

        $this->backfillCity();

        $this->backfillDestinationCountry();

        Schema::table(
            'umrah_group_flights',
            function (Blueprint $table): void {
                $table
                    ->foreign('airline_id')
                    ->references('id')
                    ->on('airlines')
                    ->restrictOnDelete();
            },
        );

        Schema::table(
            'umrah_packages',
            function (Blueprint $table): void {
                $table
                    ->foreign('airline_id')
                    ->references('id')
                    ->on('airlines')
                    ->restrictOnDelete();
            },
        );

        Schema::table(
            'umrah_hotels',
            function (Blueprint $table): void {
                $table
                    ->foreign('city_id')
                    ->references('id')
                    ->on('cities')
                    ->restrictOnDelete();
            },
        );

        Schema::table(
            'travel_insurance_policies',
            function (Blueprint $table): void {
                $table
                    ->foreign(
                        'destination_country_id',
                    )
                    ->references('id')
                    ->on('countries')
                    ->restrictOnDelete();
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'travel_insurance_policies',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'destination_country_id',
                ]);

                $table->dropColumn(
                    'destination_country_id',
                );
            },
        );

        Schema::table(
            'umrah_hotels',
            function (Blueprint $table): void {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            },
        );

        Schema::table(
            'umrah_packages',
            function (Blueprint $table): void {
                $table->dropForeign(['airline_id']);
                $table->dropColumn('airline_id');
            },
        );

        Schema::table(
            'umrah_group_flights',
            function (Blueprint $table): void {
                $table->dropForeign(['airline_id']);
                $table->dropColumn('airline_id');
            },
        );

        // Reference-master rows are intentionally preserved.
        // They may be shared by other records after this migration.
    }

    private function resolveOrCreateCountry(): int
    {
        $matches =
            DB::table('countries')
                ->where(
                    static function ($query): void {
                        $query
                            ->whereRaw(
                                'LOWER(name) = ?',
                                ['saudi arabia'],
                            )
                            ->orWhereRaw(
                                'LOWER(code) = ?',
                                ['sa'],
                            )
                            ->orWhereRaw(
                                'LOWER(iso3) = ?',
                                ['sau'],
                            );
                    },
                )
                ->get();

        if ($matches->count() > 1) {
            throw new \RuntimeException(
                'Conflicting Saudi Arabia country rows found.',
            );
        }

        if ($matches->count() === 1) {
            $row = $matches->first();

            if (
                strtoupper(
                    (string) $row->code,
                ) !== 'SA'
                || strtoupper(
                    (string) $row->iso3,
                ) !== 'SAU'
            ) {
                throw new \RuntimeException(
                    'Existing Saudi Arabia country row has conflicting codes.',
                );
            }

            DB::table('countries')
                ->where('id', $row->id)
                ->update([
                    'name' => 'Saudi Arabia',
                    'currency' => 'SAR',
                    'phone_code' => '+966',
                    'status' => true,
                    'updated_at' => now(),
                ]);

            return (int) $row->id;
        }

        return (int)
            DB::table('countries')
                ->insertGetId([
                    'name' => 'Saudi Arabia',
                    'code' => 'SA',
                    'iso3' => 'SAU',
                    'currency' => 'SAR',
                    'phone_code' => '+966',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    }

    private function resolveOrCreateAirline(): int
    {
        $matches =
            DB::table('airlines')
                ->where(
                    static function ($query): void {
                        $query
                            ->whereRaw(
                                'LOWER(name) = ?',
                                ['saudi airlines'],
                            )
                            ->orWhereRaw(
                                'LOWER(code) = ?',
                                ['sv'],
                            );
                    },
                )
                ->get();

        if ($matches->count() > 1) {
            throw new \RuntimeException(
                'Conflicting Saudi airline rows found.',
            );
        }

        if ($matches->count() === 1) {
            $row = $matches->first();

            if (
                strtoupper(
                    (string) $row->code,
                ) !== 'SV'
            ) {
                throw new \RuntimeException(
                    'Existing Saudi airline row has conflicting code.',
                );
            }

            DB::table('airlines')
                ->where('id', $row->id)
                ->update([
                    'name' => 'Saudi Airlines',
                    'country' => 'Saudi Arabia',
                    'status' => true,
                    'updated_at' => now(),
                ]);

            return (int) $row->id;
        }

        return (int)
            DB::table('airlines')
                ->insertGetId([
                    'name' => 'Saudi Airlines',
                    'code' => 'SV',
                    'country' => 'Saudi Arabia',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    }

    private function resolveOrCreateCity(
        int $countryId,
        string $name,
        string $code,
    ): int {
        $matches =
            DB::table('cities')
                ->where(
                    static function ($query) use (
                        $name,
                        $code,
                    ): void {
                        $query
                            ->whereRaw(
                                'LOWER(name) = ?',
                                [strtolower($name)],
                            )
                            ->orWhereRaw(
                                'LOWER(code) = ?',
                                [strtolower($code)],
                            );
                    },
                )
                ->get();

        if ($matches->count() > 1) {
            throw new \RuntimeException(
                "Conflicting {$name} city rows found.",
            );
        }

        if ($matches->count() === 1) {
            $row = $matches->first();

            if (
                (int) $row->country_id
                    !== $countryId
            ) {
                throw new \RuntimeException(
                    "Existing {$name} city belongs to another country.",
                );
            }

            DB::table('cities')
                ->where('id', $row->id)
                ->update([
                    'name' => $name,
                    'code' => $code,
                    'status' => 'Active',
                    'updated_at' => now(),
                ]);

            return (int) $row->id;
        }

        return (int)
            DB::table('cities')
                ->insertGetId([
                    'country_id' => $countryId,
                    'name' => $name,
                    'code' => $code,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    }

    private function backfillAirline(
        string $table,
        array $snapshotColumns,
    ): void {
        DB::table($table)
            ->orderBy('id')
            ->get()
            ->each(
                function (object $row) use (
                    $table,
                    $snapshotColumns,
                ): void {
                    $candidateValues = [];

                    foreach (
                        $snapshotColumns
                        as $column
                    ) {
                        $value =
                            trim(
                                (string)
                                    ($row->{$column} ?? ''),
                            );

                        if ($value !== '') {
                            $candidateValues[] =
                                strtolower($value);
                        }
                    }

                    if ($candidateValues === []) {
                        return;
                    }

                    $masterIds =
                        DB::table('airlines')
                            ->get([
                                'id',
                                'name',
                                'code',
                            ])
                            ->filter(
                                static function (
                                    object $airline,
                                ) use (
                                    $candidateValues,
                                ): bool {
                                    return in_array(
                                        strtolower(
                                            trim(
                                                (string)
                                                    $airline->name,
                                            ),
                                        ),
                                        $candidateValues,
                                        true,
                                    )
                                    || in_array(
                                        strtolower(
                                            trim(
                                                (string)
                                                    $airline->code,
                                            ),
                                        ),
                                        $candidateValues,
                                        true,
                                    );
                                },
                            )
                            ->pluck('id')
                            ->unique()
                            ->values();

                    if ($masterIds->count() !== 1) {
                        throw new \RuntimeException(
                            "Unable to deterministically backfill {$table} row {$row->id}.",
                        );
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'airline_id' =>
                                (int)
                                    $masterIds->first(),
                        ]);
                },
            );
    }

    private function backfillCity(): void
    {
        DB::table('umrah_hotels')
            ->orderBy('id')
            ->get()
            ->each(
                static function (
                    object $row,
                ): void {
                    $value =
                        strtolower(
                            trim(
                                (string) $row->city,
                            ),
                        );

                    if ($value === '') {
                        return;
                    }

                    $matches =
                        DB::table('cities')
                            ->whereRaw(
                                'LOWER(name) = ?',
                                [$value],
                            )
                            ->pluck('id')
                            ->unique()
                            ->values();

                    if ($matches->count() !== 1) {
                        throw new \RuntimeException(
                            "Unable to deterministically backfill umrah_hotels row {$row->id}.",
                        );
                    }

                    DB::table('umrah_hotels')
                        ->where('id', $row->id)
                        ->update([
                            'city_id' =>
                                (int)
                                    $matches->first(),
                        ]);
                },
            );
    }

    private function backfillDestinationCountry(): void
    {
        DB::table(
            'travel_insurance_policies',
        )
            ->orderBy('id')
            ->get()
            ->each(
                static function (
                    object $row,
                ): void {
                    $value =
                        strtolower(
                            trim(
                                (string)
                                    $row->destination_country,
                            ),
                        );

                    if ($value === '') {
                        return;
                    }

                    $matches =
                        DB::table('countries')
                            ->where(
                                static function (
                                    $query,
                                ) use ($value): void {
                                    $query
                                        ->whereRaw(
                                            'LOWER(name) = ?',
                                            [$value],
                                        )
                                        ->orWhereRaw(
                                            'LOWER(code) = ?',
                                            [$value],
                                        )
                                        ->orWhereRaw(
                                            'LOWER(iso3) = ?',
                                            [$value],
                                        );
                                },
                            )
                            ->pluck('id')
                            ->unique()
                            ->values();

                    if ($matches->count() !== 1) {
                        throw new \RuntimeException(
                            "Unable to deterministically backfill travel_insurance_policies row {$row->id}.",
                        );
                    }

                    DB::table(
                        'travel_insurance_policies',
                    )
                        ->where('id', $row->id)
                        ->update([
                            'destination_country_id' =>
                                (int)
                                    $matches->first(),
                        ]);
                },
            );
    }
};
