<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\UmrahGroupFlight;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class UmrahGroupFlightAirportLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_and_relationships_are_available(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'umrah_group_flights',
                [
                    'from_airport_id',
                    'to_airport_id',
                    'return_airport_id',
                ],
            ),
        );

        $model = new UmrahGroupFlight();

        $this->assertInstanceOf(
            BelongsTo::class,
            $model->fromAirport(),
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            $model->toAirport(),
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            $model->returnAirport(),
        );
    }

    public function test_selected_airports_sync_iata_snapshots_and_route(): void
    {
        $jed =
            Airport::query()
                ->where('iata_code', 'JED')
                ->firstOrFail();

        $med =
            Airport::query()
                ->where('iata_code', 'MED')
                ->firstOrFail();

        $flight =
            UmrahGroupFlight::query()
                ->create([
                    'title' =>
                        'Airport Link Test',
                    'from_airport_id' =>
                        $jed->id,
                    'to_airport_id' =>
                        $med->id,
                    'return_airport_id' =>
                        $jed->id,
                    'seats_total' => 10,
                    'seats_left' => 10,
                    'status' => 'Available',
                ]);

        $flight->refresh();

        $this->assertSame(
            'JED',
            $flight->from_city,
        );

        $this->assertSame(
            'MED',
            $flight->to_city,
        );

        $this->assertSame(
            'JED',
            $flight->return_city,
        );

        $this->assertSame(
            'JED-MED-JED',
            $flight->route,
        );
    }

    public function test_inactive_airport_is_rejected(): void
    {
        $jed =
            Airport::query()
                ->where('iata_code', 'JED')
                ->firstOrFail();

        Airport::query()
            ->where('id', $jed->id)
            ->update([
                'status' => false,
            ]);

        $this->expectException(
            ValidationException::class,
        );

        UmrahGroupFlight::query()
            ->create([
                'title' =>
                    'Inactive Airport Test',
                'to_airport_id' =>
                    $jed->id,
                'seats_total' => 1,
                'seats_left' => 1,
                'status' => 'Available',
            ]);
    }

    public function test_form_uses_airport_selectors_and_keeps_segments(): void
    {
        $form =
            file_get_contents(
                app_path(
                    'Filament/Resources/UmrahGroupFlights/Schemas/UmrahGroupFlightForm.php',
                ),
            );

        $this->assertStringContainsString(
            "Select::make('from_airport_id')",
            (string) $form,
        );

        $this->assertStringContainsString(
            "Select::make('to_airport_id')",
            (string) $form,
        );

        $this->assertStringContainsString(
            "Select::make('return_airport_id')",
            (string) $form,
        );

        $this->assertStringContainsString(
            "Repeater::make('flight_segments')",
            (string) $form,
        );

        $this->assertStringNotContainsString(
            "TextInput::make('from_city')",
            (string) $form,
        );

        $this->assertStringNotContainsString(
            "TextInput::make('to_city')",
            (string) $form,
        );

        $this->assertStringNotContainsString(
            "TextInput::make('return_city')",
            (string) $form,
        );
    }

    public function test_migration_preserves_snapshot_columns_and_segment_json(): void
    {
        $migration =
            file_get_contents(
                database_path(
                    'migrations/2026_07_22_234500_add_airport_links_to_umrah_group_flights.php',
                ),
            );

        $this->assertStringContainsString(
            "'JEDDHA' => 'JED'",
            (string) $migration,
        );

        $this->assertStringContainsString(
            "'MULTAN' => 'MUX'",
            (string) $migration,
        );

        $this->assertStringNotContainsString(
            "dropColumn('from_city')",
            (string) $migration,
        );

        $this->assertStringNotContainsString(
            "dropColumn('to_city')",
            (string) $migration,
        );

        $this->assertStringNotContainsString(
            "dropColumn('return_city')",
            (string) $migration,
        );

        $this->assertStringNotContainsString(
            "'flight_segments' =>",
            (string) $migration,
        );

        $this->assertStringNotContainsString(
            "'route' =>",
            (string) $migration,
        );
    }
}
