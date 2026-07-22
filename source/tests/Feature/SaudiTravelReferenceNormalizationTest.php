<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\City;
use App\Models\Country;
use App\Models\TravelInsurancePolicy;
use App\Models\UmrahGroupFlight;
use App\Models\UmrahHotel;
use App\Models\UmrahPackage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SaudiTravelReferenceNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_rows_and_normalized_columns_exist(): void
    {
        $saudi =
            Country::query()
                ->where('code', 'SA')
                ->where('iso3', 'SAU')
                ->firstOrFail();

        $this->assertSame(
            'Saudi Arabia',
            $saudi->name,
        );

        $this->assertSame(
            'SAR',
            $saudi->currency,
        );

        $this->assertSame(
            '+966',
            $saudi->phone_code,
        );

        $this->assertDatabaseHas(
            'airlines',
            [
                'name' => 'Saudi Airlines',
                'code' => 'SV',
                'country' => 'Saudi Arabia',
                'status' => 1,
            ],
        );

        $this->assertDatabaseHas(
            'cities',
            [
                'country_id' => $saudi->id,
                'name' => 'Makkah',
                'code' => 'SA-MAKKAH',
                'status' => 'Active',
            ],
        );

        $this->assertDatabaseHas(
            'cities',
            [
                'country_id' => $saudi->id,
                'name' => 'Madinah',
                'code' => 'SA-MADINAH',
                'status' => 'Active',
            ],
        );

        $this->assertTrue(
            Schema::hasColumns(
                'umrah_group_flights',
                ['airline_id'],
            ),
        );

        $this->assertTrue(
            Schema::hasColumns(
                'umrah_packages',
                ['airline_id'],
            ),
        );

        $this->assertTrue(
            Schema::hasColumns(
                'umrah_hotels',
                ['city_id'],
            ),
        );

        $this->assertTrue(
            Schema::hasColumns(
                'travel_insurance_policies',
                ['destination_country_id'],
            ),
        );
    }

    public function test_group_flight_and_package_sync_airline_snapshots(): void
    {
        $airline =
            Airline::query()
                ->where('code', 'SV')
                ->firstOrFail();

        $group =
            UmrahGroupFlight::query()
                ->create([
                    'title' => 'Normalization Test',
                    'airline_id' => $airline->id,
                    'seats_total' => 10,
                    'seats_left' => 10,
                    'status' => 'Available',
                ]);

        $this->assertSame(
            'Saudi Airlines',
            $group->fresh()->airline,
        );

        $this->assertSame(
            'SV',
            $group->fresh()->airline_code,
        );

        $package =
            UmrahPackage::query()
                ->create([
                    'title' => 'Normalization Package',
                    'umrah_group_flight_id' => $group->id,
                    'sharing_price' => 100000,
                    'status' => 'Available',
                ]);

        $package->refresh();

        $this->assertSame(
            $airline->id,
            $package->airline_id,
        );

        $this->assertSame(
            'Saudi Airlines',
            $package->airline,
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            $group->airlineMaster(),
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            $package->airlineMaster(),
        );
    }

    public function test_umrah_hotel_syncs_city_snapshot(): void
    {
        $city =
            City::query()
                ->where('code', 'SA-MAKKAH')
                ->firstOrFail();

        $hotel =
            UmrahHotel::query()
                ->create([
                    'city_id' => $city->id,
                    'hotel_name' => 'Normalization Hotel',
                    'status' => 'Active',
                ]);

        $hotel->refresh();

        $this->assertSame(
            'Makkah',
            $hotel->city,
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            $hotel->cityMaster(),
        );
    }

    public function test_insurance_policy_exposes_destination_country_relationship(): void
    {
        $policy =
            new TravelInsurancePolicy();

        $this->assertInstanceOf(
            BelongsTo::class,
            $policy->destinationCountry(),
        );
    }

    public function test_filament_forms_use_reference_master_selectors(): void
    {
        $groupForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/UmrahGroupFlights/Schemas/UmrahGroupFlightForm.php',
                ),
            );

        $packageForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/UmrahPackages/Schemas/UmrahPackageForm.php',
                ),
            );

        $hotelForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/UmrahHotels/Schemas/UmrahHotelForm.php',
                ),
            );

        $insuranceForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/TravelInsurancePolicies/Schemas/TravelInsurancePolicyForm.php',
                ),
            );

        $this->assertStringContainsString(
            "Select::make('airline_id')",
            (string) $groupForm,
        );

        $this->assertStringContainsString(
            "\$set('airline_id', \$groupFlight?->airline_id);",
            (string) $packageForm,
        );

        $this->assertStringContainsString(
            "Select::make('city_id')",
            (string) $hotelForm,
        );

        $this->assertStringContainsString(
            "'destination_country_id'",
            (string) $insuranceForm,
        );

        $this->assertStringNotContainsString(
            "Select::make('city')",
            (string) $hotelForm,
        );
    }
}
