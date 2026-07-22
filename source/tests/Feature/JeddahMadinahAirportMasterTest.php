<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class JeddahMadinahAirportMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_jeddah_city_is_linked_to_saudi_arabia(): void
    {
        $saudi =
            Country::query()
                ->where('code', 'SA')
                ->where('iso3', 'SAU')
                ->firstOrFail();

        $jeddah =
            City::query()
                ->where('code', 'SA-JEDDAH')
                ->firstOrFail();

        $this->assertSame(
            'Jeddah',
            $jeddah->name,
        );

        $this->assertSame(
            'Active',
            $jeddah->status,
        );

        $this->assertSame(
            $saudi->id,
            $jeddah->country_id,
        );

        $this->assertSame(
            1,
            City::query()
                ->where('code', 'SA-JEDDAH')
                ->count(),
        );
    }

    public function test_jed_and_med_airports_have_deterministic_master_links(): void
    {
        $saudi =
            Country::query()
                ->where('code', 'SA')
                ->firstOrFail();

        $jeddah =
            City::query()
                ->where('code', 'SA-JEDDAH')
                ->firstOrFail();

        $madinah =
            City::query()
                ->where('code', 'SA-MADINAH')
                ->firstOrFail();

        $jed =
            Airport::query()
                ->where('iata_code', 'JED')
                ->firstOrFail();

        $med =
            Airport::query()
                ->where('iata_code', 'MED')
                ->firstOrFail();

        $this->assertSame(
            'JED - Jeddah',
            $jed->name,
        );

        $this->assertSame(
            'Jeddah',
            $jed->city,
        );

        $this->assertSame(
            $jeddah->id,
            $jed->city_id,
        );

        $this->assertSame(
            $saudi->id,
            $jed->country_id,
        );

        $this->assertNull(
            $jed->icao_code,
        );

        $this->assertSame(
            'MED - Madinah',
            $med->name,
        );

        $this->assertSame(
            'Madinah',
            $med->city,
        );

        $this->assertSame(
            $madinah->id,
            $med->city_id,
        );

        $this->assertSame(
            $saudi->id,
            $med->country_id,
        );

        $this->assertNull(
            $med->icao_code,
        );
    }

    public function test_airport_codes_are_unique_and_active(): void
    {
        foreach (['JED', 'MED'] as $code) {
            $this->assertSame(
                1,
                Airport::query()
                    ->where('iata_code', $code)
                    ->count(),
            );

            $this->assertDatabaseHas(
                'airports',
                [
                    'iata_code' => $code,
                    'status' => true,
                ],
            );
        }
    }

    public function test_airport_model_exposes_city_and_country_relationships(): void
    {
        $jed =
            Airport::query()
                ->where('iata_code', 'JED')
                ->firstOrFail();

        $this->assertSame(
            'SA-JEDDAH',
            $jed->cityMaster?->code,
        );

        $this->assertSame(
            'SA',
            $jed->country?->code,
        );
    }

    public function test_migration_preserves_reference_rows_on_rollback_contract(): void
    {
        $migration =
            file_get_contents(
                database_path(
                    'migrations/2026_07_22_231500_create_jeddah_madinah_airport_masters.php',
                ),
            );

        $this->assertStringContainsString(
            'Reference-master rows are intentionally preserved.',
            (string) $migration,
        );

        $this->assertStringNotContainsString(
            "->delete();",
            (string) $migration,
        );
    }
}
