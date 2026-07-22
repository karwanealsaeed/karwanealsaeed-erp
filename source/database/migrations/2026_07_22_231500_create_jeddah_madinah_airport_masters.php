<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'countries',
                'cities',
                'airports',
            ]
            as $table
        ) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException(
                    "Required table missing: {$table}",
                );
            }
        }

        $saudiId =
            $this->resolveSaudiCountry();

        $jeddahId =
            $this->resolveOrCreateJeddah(
                $saudiId,
            );

        $madinahId =
            $this->resolveMadinah(
                $saudiId,
            );

        $this->resolveOrCreateAirport(
            code: 'JED',
            name: 'JED - Jeddah',
            cityName: 'Jeddah',
            cityId: $jeddahId,
            countryId: $saudiId,
        );

        $this->resolveOrCreateAirport(
            code: 'MED',
            name: 'MED - Madinah',
            cityName: 'Madinah',
            cityId: $madinahId,
            countryId: $saudiId,
        );
    }

    public function down(): void
    {
        // Reference-master rows are intentionally preserved.
        // They may be shared by operational records.
    }

    private function resolveSaudiCountry(): int
    {
        $matches =
            DB::table('countries')
                ->where(
                    static function ($query): void {
                        $query
                            ->whereRaw(
                                'LOWER(code) = ?',
                                ['sa'],
                            )
                            ->orWhereRaw(
                                'LOWER(iso3) = ?',
                                ['sau'],
                            )
                            ->orWhereRaw(
                                'LOWER(name) = ?',
                                ['saudi arabia'],
                            );
                    },
                )
                ->get();

        if ($matches->count() !== 1) {
            throw new \RuntimeException(
                'Saudi Arabia country master is missing or ambiguous.',
            );
        }

        $row = $matches->first();

        if (
            strtoupper((string) $row->code) !== 'SA'
            || strtoupper((string) $row->iso3)
                !== 'SAU'
        ) {
            throw new \RuntimeException(
                'Saudi Arabia country codes conflict.',
            );
        }

        return (int) $row->id;
    }

    private function resolveOrCreateJeddah(
        int $countryId,
    ): int {
        $matches =
            DB::table('cities')
                ->where(
                    static function ($query): void {
                        $query
                            ->whereRaw(
                                'LOWER(name) IN (?, ?, ?)',
                                [
                                    'jeddah',
                                    'jeddha',
                                    'jeddah city',
                                ],
                            )
                            ->orWhereRaw(
                                'LOWER(code) IN (?, ?)',
                                [
                                    'sa-jeddah',
                                    'sa-jed',
                                ],
                            );
                    },
                )
                ->get();

        if ($matches->count() > 1) {
            throw new \RuntimeException(
                'Conflicting Jeddah city rows found.',
            );
        }

        if ($matches->count() === 1) {
            $row = $matches->first();

            if (
                (int) $row->country_id
                    !== $countryId
            ) {
                throw new \RuntimeException(
                    'Existing Jeddah city belongs to another country.',
                );
            }

            DB::table('cities')
                ->where('id', $row->id)
                ->update([
                    'name' => 'Jeddah',
                    'code' => 'SA-JEDDAH',
                    'status' => 'Active',
                    'updated_at' => now(),
                ]);

            return (int) $row->id;
        }

        return (int)
            DB::table('cities')
                ->insertGetId([
                    'country_id' => $countryId,
                    'name' => 'Jeddah',
                    'code' => 'SA-JEDDAH',
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    }

    private function resolveMadinah(
        int $countryId,
    ): int {
        $matches =
            DB::table('cities')
                ->where(
                    static function ($query): void {
                        $query
                            ->whereRaw(
                                'LOWER(name) IN (?, ?, ?)',
                                [
                                    'madinah',
                                    'madina',
                                    'medina',
                                ],
                            )
                            ->orWhereRaw(
                                'LOWER(code) IN (?, ?)',
                                [
                                    'sa-madinah',
                                    'sa-med',
                                ],
                            );
                    },
                )
                ->get();

        if ($matches->count() !== 1) {
            throw new \RuntimeException(
                'Madinah city master is missing or ambiguous.',
            );
        }

        $row = $matches->first();

        if (
            (int) $row->country_id
                !== $countryId
        ) {
            throw new \RuntimeException(
                'Madinah city belongs to another country.',
            );
        }

        if (
            strtoupper((string) $row->code)
                !== 'SA-MADINAH'
        ) {
            throw new \RuntimeException(
                'Madinah city code conflicts.',
            );
        }

        return (int) $row->id;
    }

    private function resolveOrCreateAirport(
        string $code,
        string $name,
        string $cityName,
        int $cityId,
        int $countryId,
    ): int {
        $matches =
            DB::table('airports')
                ->where(
                    static function ($query) use (
                        $code,
                        $name,
                        $cityName,
                    ): void {
                        $query
                            ->whereRaw(
                                'LOWER(iata_code) = ?',
                                [strtolower($code)],
                            )
                            ->orWhereRaw(
                                'LOWER(name) IN (?, ?)',
                                [
                                    strtolower($name),
                                    strtolower($cityName),
                                ],
                            );
                    },
                )
                ->get();

        if ($matches->count() > 1) {
            throw new \RuntimeException(
                "Conflicting {$code} airport rows found.",
            );
        }

        if ($matches->count() === 1) {
            $row = $matches->first();

            if (
                (int) $row->country_id
                    !== $countryId
                || (int) $row->city_id
                    !== $cityId
            ) {
                throw new \RuntimeException(
                    "Existing {$code} airport links conflict.",
                );
            }

            DB::table('airports')
                ->where('id', $row->id)
                ->update([
                    'name' => $name,
                    'iata_code' => $code,
                    'icao_code' => null,
                    'city' => $cityName,
                    'status' => true,
                    'updated_at' => now(),
                ]);

            return (int) $row->id;
        }

        return (int)
            DB::table('airports')
                ->insertGetId([
                    'name' => $name,
                    'iata_code' => $code,
                    'icao_code' => null,
                    'city' => $cityName,
                    'city_id' => $cityId,
                    'country_id' => $countryId,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    }
};
