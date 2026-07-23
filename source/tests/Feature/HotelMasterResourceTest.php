<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Hotels\HotelResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\Supplier;
use App\Observers\HotelObserver;
use App\Services\MasterCodeGenerator;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class HotelMasterResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_hotel_schema_and_booking_links_exist(): void
    {
        $this->assertTrue(
            Schema::hasTable('hotels'),
        );

        foreach (
            [
                'hotel_code',
                'hotel_name',
                'city_id',
                'supplier_id',
                'star_rating',
                'status',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'hotels',
                    $column,
                ),
            );
        }

        $this->assertTrue(
            Schema::hasColumn(
                'hotel_bookings',
                'hotel_id',
            ),
        );

        $this->assertTrue(
            Schema::hasColumn(
                'hotel_bookings',
                'city_id',
            ),
        );

        $this->assertFalse(
            Schema::hasColumn(
                'hotels',
                'country_id',
            ),
        );
    }

    public function test_hotel_generator_and_observer_create_immutable_codes(): void
    {
        [$country, $city, $supplier] =
            $this->references();

        $generator =
            app(MasterCodeGenerator::class);

        $this->assertSame(
            'HTL-000001',
            $generator->nextHotelCode(),
        );

        $hotel =
            Hotel::query()
                ->create([
                    'hotel_name' =>
                        'Grand Test Hotel',
                    'city_id' =>
                        $city->id,
                    'supplier_id' =>
                        $supplier->id,
                    'star_rating' =>
                        5,
                    'status' =>
                        'Active',
                ]);

        $this->assertSame(
            'HTL-000002',
            $hotel->hotel_code,
        );

        $hotel->hotel_code =
            'HTL-999999';

        $this->expectException(
            ValidationException::class,
        );

        app(HotelObserver::class)
            ->updating($hotel);
    }

    public function test_hotel_observer_requires_active_city_and_supplier(): void
    {
        [, $city, $supplier] =
            $this->references();

        $city->status = 'Inactive';
        $city->save();

        $hotel =
            new Hotel([
                'hotel_name' =>
                    'Invalid Hotel',
                'city_id' =>
                    $city->id,
                'supplier_id' =>
                    $supplier->id,
                'star_rating' =>
                    4,
                'status' =>
                    'Active',
            ]);

        $this->expectException(
            ValidationException::class,
        );

        app(HotelObserver::class)
            ->saving($hotel);
    }

    public function test_selected_hotel_syncs_booking_snapshots(): void
    {
        [$country, $city, $supplier] =
            $this->references();

        $hotel =
            Hotel::query()
                ->create([
                    'hotel_name' =>
                        'Normalized Hotel',
                    'city_id' =>
                        $city->id,
                    'supplier_id' =>
                        $supplier->id,
                    'star_rating' =>
                        4,
                    'status' =>
                        'Active',
                ]);

        $customer =
            Customer::query()
                ->create([
                    'name' =>
                        'Hotel Customer',
                    'customer_type' =>
                        'Customer',
                    'mobile' =>
                        '03000001001',
                    'country' =>
                        'Pakistan',
                    'status' =>
                        'Active',
                ]);

        $booking =
            HotelBooking::query()
                ->create([
                    'booking_no' =>
                        'HB-TEST-001',
                    'customer_id' =>
                        $customer->id,
                    'hotel_id' =>
                        $hotel->id,
                    'check_in_date' =>
                        now()->addDay()->toDateString(),
                    'check_out_date' =>
                        now()->addDays(2)->toDateString(),
                    'no_of_rooms' =>
                        1,
                    'no_of_persons' =>
                        2,
                    'total_amount' =>
                        10000,
                    'paid_amount' =>
                        0,
                    'balance_amount' =>
                        10000,
                    'status' =>
                        'Pending',
                ]);

        $this->assertSame(
            $hotel->id,
            $booking->hotel_id,
        );

        $this->assertSame(
            $city->id,
            $booking->city_id,
        );

        $this->assertSame(
            $country->id,
            $booking->country_id,
        );

        $this->assertSame(
            $city->name,
            $booking->city,
        );

        $this->assertSame(
            $hotel->hotel_name,
            $booking->hotel_name,
        );

        $this->assertTrue(
            $booking->hotel->is($hotel),
        );

        $this->assertTrue(
            $booking->cityMaster->is($city),
        );
    }

    public function test_legacy_free_text_hotel_booking_remains_supported(): void
    {
        [$country] =
            $this->references();

        $customer =
            Customer::query()
                ->create([
                    'name' =>
                        'Legacy Customer',
                    'customer_type' =>
                        'Customer',
                    'mobile' =>
                        '03000001002',
                    'country' =>
                        'Pakistan',
                    'status' =>
                        'Active',
                ]);

        $booking =
            HotelBooking::query()
                ->create([
                    'booking_no' =>
                        'HB-LEGACY-001',
                    'customer_id' =>
                        $customer->id,
                    'hotel_id' =>
                        null,
                    'city_id' =>
                        null,
                    'country_id' =>
                        $country->id,
                    'city' =>
                        'Legacy City',
                    'hotel_name' =>
                        'Legacy Hotel Snapshot',
                    'check_in_date' =>
                        now()->addDay()->toDateString(),
                    'no_of_rooms' =>
                        1,
                    'no_of_persons' =>
                        1,
                    'total_amount' =>
                        5000,
                    'paid_amount' =>
                        0,
                    'balance_amount' =>
                        5000,
                    'status' =>
                        'Pending',
                ]);

        $this->assertNull(
            $booking->hotel_id,
        );

        $this->assertNull(
            $booking->city_id,
        );

        $this->assertSame(
            'Legacy City',
            $booking->city,
        );

        $this->assertSame(
            'Legacy Hotel Snapshot',
            $booking->hotel_name,
        );
    }

    public function test_hotel_permissions_and_role_defaults_follow_booking_parity(): void
    {
        $options =
            UserForm::getPermissionOptions();

        foreach (
            [
                'hotels_view',
                'hotels_create',
                'hotels_edit',
                'hotels_delete',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );
        }

        foreach (
            [
                'Manager',
                'Accountant',
                'Sales Staff',
            ]
            as $role
        ) {
            $defaults =
                UserForm::getDefaultPermissions(
                    $role,
                );

            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $this->assertSame(
                    in_array(
                        "hotel_bookings_{$action}",
                        $defaults,
                        true,
                    ),
                    in_array(
                        "hotels_{$action}",
                        $defaults,
                        true,
                    ),
                    "{$role} Hotel permission parity failed for {$action}.",
                );
            }
        }

        $this->assertContains(
            HotelResource::class,
            RbacPermissionRegistry::resourceClasses(),
        );
    }

    public function test_hotel_resource_routes_and_ui_contract_are_registered(): void
    {
        foreach (
            [
                'filament.admin.resources.hotels.index',
                'filament.admin.resources.hotels.create',
                'filament.admin.resources.hotels.view',
                'filament.admin.resources.hotels.edit',
            ]
            as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                "Missing route: {$routeName}",
            );
        }

        $form =
            file_get_contents(
                app_path(
                    'Filament/Resources/Hotels/Schemas/HotelForm.php',
                ),
            );

        $bookingForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/HotelBookings/Schemas/HotelBookingForm.php',
                ),
            );

        $this->assertIsString($form);
        $this->assertIsString($bookingForm);

        $this->assertStringContainsString(
            "Select::make('city_id')",
            $form,
        );

        $this->assertStringContainsString(
            "Select::make('supplier_id')",
            $form,
        );

        $this->assertStringContainsString(
            "Select::make('star_rating')",
            $form,
        );

        $this->assertStringContainsString(
            "Select::make('hotel_id')",
            $bookingForm,
        );

        $this->assertStringContainsString(
            "Select::make('city_id')",
            $bookingForm,
        );
    }

    public function test_umrah_hotel_master_remains_separate(): void
    {
        $this->assertTrue(
            Schema::hasTable(
                'umrah_hotels',
            ),
        );

        $this->assertTrue(
            Schema::hasColumn(
                'umrah_hotels',
                'has_similar',
            ),
        );

        $this->assertTrue(
            Schema::hasColumn(
                'umrah_packages',
                'makkah_hotel_id',
            ),
        );

        $this->assertFalse(
            Schema::hasColumn(
                'umrah_hotels',
                'hotel_code',
            ),
        );
    }

    private function references(): array
    {
        $country =
            Country::query()
                ->create([
                    'name' =>
                        'Hotel Test Country',
                    'code' =>
                        'HT',
                    'iso3' =>
                        'HTL',
                ]);

        $city =
            City::query()
                ->create([
                    'country_id' =>
                        $country->id,
                    'name' =>
                        'Hotel Test City',
                    'status' =>
                        'Active',
                ]);

        $supplier =
            Supplier::query()
                ->create([
                    'name' =>
                        'Hotel Supplier Contact',
                    'company_name' =>
                        'Hotel Supplier Company',
                    'mobile' =>
                        '03000001999',
                    'country' =>
                        'Hotel Test Country',
                    'supplier_type' =>
                        'Hotel',
                    'status' =>
                        'Active',
                ]);

        return [
            $country,
            $city,
            $supplier,
        ];
    }
}