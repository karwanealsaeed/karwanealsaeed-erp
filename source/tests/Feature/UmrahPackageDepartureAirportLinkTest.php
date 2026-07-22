<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\UmrahGroupFlight;
use App\Models\UmrahPackage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class UmrahPackageDepartureAirportLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_and_relationship_are_available(): void
    {
        $this->assertTrue(
            Schema::hasColumn(
                'umrah_packages',
                'departure_airport_id',
            ),
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new UmrahPackage())
                ->departureAirport(),
        );
    }

    public function test_linked_package_derives_departure_airport_from_group(): void
    {
        $jed =
            Airport::query()
                ->where('iata_code', 'JED')
                ->firstOrFail();

        $med =
            Airport::query()
                ->where('iata_code', 'MED')
                ->firstOrFail();

        $group =
            UmrahGroupFlight::query()
                ->create([
                    'title' =>
                        'Package Departure Link Group',
                    'from_airport_id' =>
                        $jed->id,
                    'to_airport_id' =>
                        $med->id,
                    'return_airport_id' =>
                        $jed->id,
                    'seats_total' => 20,
                    'seats_left' => 20,
                    'status' => 'Available',
                ]);

        $package =
            UmrahPackage::query()
                ->create([
                    'umrah_group_flight_id' =>
                        $group->id,
                    'title' =>
                        'Linked Departure Package',
                    'status' => 'Available',
                ]);

        $package->refresh();

        $this->assertSame(
            $jed->id,
            $package->departure_airport_id,
        );

        $this->assertSame(
            'JED',
            $package->departure_city,
        );

        $this->assertSame(
            'JED-MED-JED',
            $package->flight_route,
        );

        $this->assertSame(
            'JED',
            $package->departureAirport?->iata_code,
        );
    }

    public function test_standalone_composite_package_remains_unlinked(): void
    {
        $package =
            UmrahPackage::query()
                ->create([
                    'title' =>
                        'Standalone Composite Package',
                    'departure_city' =>
                        'Mux-Med-Mux',
                    'flight_route' =>
                        'Mux-Med-Mux',
                    'departure_code' =>
                        'PK-715',
                    'arrival_code' =>
                        'PK-716',
                    'status' => 'Available',
                ]);

        $package->refresh();

        $this->assertNull(
            $package->departure_airport_id,
        );

        $this->assertSame(
            'Mux-Med-Mux',
            $package->departure_city,
        );

        $this->assertSame(
            'Mux-Med-Mux',
            $package->flight_route,
        );

        $this->assertSame(
            'PK-715',
            $package->departure_code,
        );

        $this->assertSame(
            'PK-716',
            $package->arrival_code,
        );
    }

    public function test_inactive_group_departure_airport_is_rejected(): void
    {
        $jed =
            Airport::query()
                ->where('iata_code', 'JED')
                ->firstOrFail();

        $med =
            Airport::query()
                ->where('iata_code', 'MED')
                ->firstOrFail();

        $group =
            UmrahGroupFlight::query()
                ->create([
                    'title' =>
                        'Inactive Departure Group',
                    'from_airport_id' =>
                        $jed->id,
                    'to_airport_id' =>
                        $med->id,
                    'return_airport_id' =>
                        $jed->id,
                    'seats_total' => 1,
                    'seats_left' => 1,
                    'status' => 'Available',
                ]);

        DB::table('airports')
            ->where('id', $jed->id)
            ->update([
                'status' => false,
            ]);

        $this->expectException(
            ValidationException::class,
        );

        UmrahPackage::query()
            ->create([
                'umrah_group_flight_id' =>
                    $group->id,
                'title' =>
                    'Rejected Departure Package',
                'status' => 'Available',
            ]);
    }

    public function test_form_exposes_read_only_derived_airport(): void
    {
        $form =
            file_get_contents(
                app_path(
                    'Filament/Resources/UmrahPackages/Schemas/UmrahPackageForm.php',
                ),
            );

        $this->assertStringContainsString(
            "Select::make('departure_airport_id')",
            (string) $form,
        );

        $this->assertStringContainsString(
            "\$groupFlight?->from_airport_id",
            (string) $form,
        );

        $this->assertStringContainsString(
            'Composite standalone route ko ek airport mein force nahi kiya jata.',
            (string) $form,
        );
    }

    public function test_migration_preserves_package_snapshots(): void
    {
        $migration =
            file_get_contents(
                database_path(
                    'migrations/2026_07_22_235900_add_departure_airport_link_to_umrah_packages.php',
                ),
            );

        $this->assertStringContainsString(
            "'from_airport_id'",
            (string) $migration,
        );

        $this->assertStringContainsString(
            "whereNotNull(\n                'umrah_group_flight_id',",
            (string) $migration,
        );

        foreach (
            [
                'departure_city',
                'flight_route',
                'departure_code',
                'arrival_code',
            ]
            as $snapshot
        ) {
            $this->assertStringNotContainsString(
                "'{$snapshot}' =>",
                (string) $migration,
            );
        }
    }
}
