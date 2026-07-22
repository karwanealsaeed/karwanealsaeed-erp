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
        Schema::create(
            'cities',
            function (Blueprint $table): void {
                $table->id();
                $table
                    ->foreignId('country_id')
                    ->constrained('countries')
                    ->restrictOnDelete();
                $table->string('name', 150);
                $table->string('code', 100)->unique();
                $table
                    ->string('status', 20)
                    ->default('Active');
                $table->timestamps();

                $table->unique(
                    ['country_id', 'name'],
                    'cities_country_name_unique',
                );
                $table->index(
                    ['country_id', 'status'],
                    'cities_country_status_index',
                );
            },
        );

        Schema::table(
            'airports',
            function (Blueprint $table): void {
                $table
                    ->foreignId('city_id')
                    ->nullable()
                    ->after('city')
                    ->constrained('cities')
                    ->restrictOnDelete();
            },
        );

        $airports =
            DB::table('airports')
                ->select([
                    'id',
                    'city',
                    'country_id',
                ])
                ->orderBy('id')
                ->get();

        foreach ($airports as $airport) {
            $cityName =
                trim((string) $airport->city);

            $countryId =
                (int) $airport->country_id;

            if (
                $cityName === ''
                || $countryId < 1
            ) {
                throw new RuntimeException(
                    "Airport {$airport->id} has no deterministic city/country backfill.",
                );
            }

            $country =
                DB::table('countries')
                    ->where('id', $countryId)
                    ->first();

            if (! is_object($country)) {
                throw new RuntimeException(
                    "Airport {$airport->id} references a missing country.",
                );
            }

            $existingCityId =
                DB::table('cities')
                    ->where('country_id', $countryId)
                    ->whereRaw(
                        'LOWER(name) = ?',
                        [mb_strtolower($cityName)],
                    )
                    ->value('id');

            if ($existingCityId === null) {
                $countryCode =
                    strtoupper(
                        trim(
                            (string)
                                (
                                    $country->code
                                    ?? $country->iso3
                                    ?? 'XX'
                                ),
                        ),
                    );

                $slug =
                    strtoupper(
                        preg_replace(
                            '/[^A-Z0-9]+/',
                            '-',
                            strtoupper($cityName),
                        ) ?? '',
                    );

                $slug = trim($slug, '-');

                $baseCode =
                    trim(
                        $countryCode.'-'.$slug,
                        '-',
                    );

                $code = $baseCode;
                $suffix = 2;

                while (
                    DB::table('cities')
                        ->where('code', $code)
                        ->exists()
                ) {
                    $code =
                        $baseCode.'-'.$suffix;
                    $suffix++;
                }

                $existingCityId =
                    DB::table('cities')
                        ->insertGetId([
                            'country_id' => $countryId,
                            'name' => $cityName,
                            'code' => $code,
                            'status' => 'Active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
            }

            DB::table('airports')
                ->where('id', $airport->id)
                ->update([
                    'city_id' =>
                        (int) $existingCityId,
                ]);
        }

        $unresolved =
            DB::table('airports')
                ->whereNull('city_id')
                ->count();

        if ($unresolved > 0) {
            throw new RuntimeException(
                "Airport city backfill left {$unresolved} unresolved row(s).",
            );
        }
    }

    public function down(): void
    {
        Schema::table(
            'airports',
            function (Blueprint $table): void {
                $table
                    ->dropConstrainedForeignId(
                        'city_id',
                    );
            },
        );

        Schema::dropIfExists('cities');
    }
};
