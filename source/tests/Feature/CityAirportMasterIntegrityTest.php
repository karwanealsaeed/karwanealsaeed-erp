<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\City;
use App\Observers\AirportObserver;
use App\Observers\CityObserver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CityAirportMasterIntegrityTest extends TestCase
{
    private string $previousDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDefaultConnection =
            DB::getDefaultConnection();

        config([
            'database.connections.phase13c_city_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('phase13c_city_test');
        DB::setDefaultConnection(
            'phase13c_city_test',
        );

        Schema::create(
            'countries',
            function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code');
                $table->string('iso3')->nullable();
                $table->timestamps();
            },
        );

        Schema::create(
            'airports',
            function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('iata_code');
                $table->string('icao_code')->nullable();
                $table->string('city');
                $table
                    ->foreignId('country_id')
                    ->constrained('countries');
                $table
                    ->string('status')
                    ->default('Active');
                $table->timestamps();
            },
        );

        DB::table('countries')->insert([
            'id' => 2,
            'name' => 'Pakistan',
            'code' => 'PK',
            'iso3' => 'PAK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('airports')->insert([
            [
                'id' => 10,
                'name' => 'Multan International Airport',
                'iata_code' => 'MUX',
                'icao_code' => null,
                'city' => 'Multan',
                'country_id' => 2,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 20,
                'name' => 'Allama Iqbal International Airport',
                'iata_code' => 'LHE',
                'icao_code' => null,
                'city' => 'Lahore',
                'country_id' => 2,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $migration =
            require base_path(
                'database/migrations/2026_07_22_213045_create_cities_and_link_airports.php',
            );

        $migration->up();
    }

    protected function tearDown(): void
    {
        Schema::table(
            'airports',
            function (Blueprint $table): void {
                if (
                    Schema::hasColumn(
                        'airports',
                        'city_id',
                    )
                ) {
                    $table
                        ->dropConstrainedForeignId(
                            'city_id',
                        );
                }
            },
        );

        Schema::dropIfExists('cities');
        Schema::dropIfExists('airports');
        Schema::dropIfExists('countries');

        DB::disconnect(
            'phase13c_city_test',
        );
        DB::purge(
            'phase13c_city_test',
        );
        DB::setDefaultConnection(
            $this->previousDefaultConnection,
        );

        parent::tearDown();
    }

    public function test_migration_creates_cities_and_backfills_airports(): void
    {
        $this->assertTrue(
            Schema::hasTable('cities'),
        );

        $this->assertTrue(
            Schema::hasColumn(
                'airports',
                'city_id',
            ),
        );

        $this->assertDatabaseHas(
            'cities',
            [
                'country_id' => 2,
                'name' => 'Multan',
                'code' => 'PK-MULTAN',
            ],
        );

        $this->assertDatabaseHas(
            'cities',
            [
                'country_id' => 2,
                'name' => 'Lahore',
                'code' => 'PK-LAHORE',
            ],
        );

        $multanCityId =
            DB::table('cities')
                ->where('code', 'PK-MULTAN')
                ->value('id');

        $lahoreCityId =
            DB::table('cities')
                ->where('code', 'PK-LAHORE')
                ->value('id');

        $this->assertDatabaseHas(
            'airports',
            [
                'id' => 10,
                'city' => 'Multan',
                'city_id' => $multanCityId,
            ],
        );

        $this->assertDatabaseHas(
            'airports',
            [
                'id' => 20,
                'city' => 'Lahore',
                'city_id' => $lahoreCityId,
            ],
        );

        $this->assertSame(
            0,
            DB::table('airports')
                ->whereNull('city_id')
                ->count(),
        );
    }

    public function test_icao_remains_optional(): void
    {
        $this->assertDatabaseHas(
            'airports',
            [
                'id' => 10,
                'icao_code' => null,
            ],
        );

        $this->assertDatabaseHas(
            'airports',
            [
                'id' => 20,
                'icao_code' => null,
            ],
        );
    }

    public function test_city_observer_generates_immutable_code(): void
    {
        $city = new City([
            'country_id' => 2,
            'name' => 'Sahiwal',
            'status' => 'Active',
        ]);

        app(CityObserver::class)
            ->saving($city);

        app(CityObserver::class)
            ->creating($city);

        $this->assertSame(
            'PK-SAHIWAL',
            $city->code,
        );

        $city->setRawAttributes(
            [
                'code' => 'PK-SAHIWAL',
            ],
            true,
        );

        $city->code = 'PK-OTHER';

        $this->expectException(
            ValidationException::class,
        );

        app(CityObserver::class)
            ->updating($city);
    }

    public function test_airport_observer_syncs_and_validates_city_country(): void
    {
        $cityId =
            DB::table('cities')
                ->where('code', 'PK-MULTAN')
                ->value('id');

        $airport = new Airport([
            'country_id' => 2,
            'city_id' => $cityId,
        ]);

        app(AirportObserver::class)
            ->saving($airport);

        $this->assertSame(
            'Multan',
            $airport->city,
        );

        DB::table('countries')->insert([
            'id' => 3,
            'name' => 'Saudi Arabia',
            'code' => 'SA',
            'iso3' => 'SAU',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $airport->country_id = 3;

        $this->expectException(
            ValidationException::class,
        );

        app(AirportObserver::class)
            ->saving($airport);
    }

    public function test_models_expose_city_relationships(): void
    {
        $city =
            City::query()
                ->where('code', 'PK-MULTAN')
                ->firstOrFail();

        $airport =
            Airport::query()
                ->where('iata_code', 'MUX')
                ->firstOrFail();

        $this->assertSame(
            'Pakistan',
            $city->country->name,
        );

        $this->assertSame(
            'Multan',
            $airport->cityMaster->name,
        );

        $this->assertCount(
            1,
            $city->airports,
        );
    }

    public function test_city_and_airport_ui_contracts_exist(): void
    {
        $cityResource =
            file_get_contents(
                app_path(
                    'Filament/Resources/Cities/CityResource.php',
                ),
            );

        $cityForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/Cities/Schemas/CityForm.php',
                ),
            );

        $cityTable =
            file_get_contents(
                app_path(
                    'Filament/Resources/Cities/Tables/CitiesTable.php',
                ),
            );

        $airportForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/Airports/Schemas/AirportForm.php',
                ),
            );

        $this->assertIsString($cityResource);
        $this->assertIsString($cityForm);
        $this->assertIsString($cityTable);
        $this->assertIsString($airportForm);

        $this->assertContains(
            \App\Filament\Resources\Cities\CityResource::class,
            \App\Support\RbacPermissionRegistry::resourceClasses(),
        );

        $this->assertStringContainsString(
            "Select::make('country_id')",
            $cityForm,
        );

        $this->assertStringContainsString(
            "TextInput::make('code')",
            $cityForm,
        );

        $this->assertStringContainsString(
            "TextColumn::make('country.name')",
            $cityTable,
        );

        $this->assertStringContainsString(
            "Select::make('city_id')",
            $airportForm,
        );

        $this->assertStringContainsString(
            "where('country_id'",
            $airportForm,
        );

        $this->assertStringNotContainsString(
            "TextInput::make('city')",
            $airportForm,
        );
    }

    public function test_city_permissions_and_routes_are_registered(): void
    {
        $userForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/Users/Schemas/UserForm.php',
                ),
            );

        $this->assertIsString($userForm);

        foreach (
            [
                'cities_view',
                'cities_create',
                'cities_edit',
                'cities_delete',
            ]
            as $permission
        ) {
            $this->assertStringContainsString(
                $permission,
                $userForm,
            );
        }

        $cityRoutes = [];

        foreach (
            app('router')->getRoutes()
            as $route
        ) {
            $name =
                (string) ($route->getName() ?? '');

            if (
                str_contains(
                    $name,
                    '.cities.',
                )
            ) {
                $cityRoutes[] = $name;
            }
        }

        $this->assertCount(
            4,
            array_values(
                array_unique($cityRoutes),
            ),
        );
    }
}
