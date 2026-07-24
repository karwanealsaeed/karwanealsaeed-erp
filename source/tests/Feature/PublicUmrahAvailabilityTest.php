<?php

namespace Tests\Feature;

use App\Models\UmrahPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUmrahAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_listing_hides_expired_packages(): void
    {
        $future = $this->createPackage([
            'package_no' => 'PUBLIC-FUTURE-001',
            'title' => 'Future Public Umrah Package',
            'start_date' => today()->addMonth()->toDateString(),
            'end_date' => today()->addMonth()->addDays(10)->toDateString(),
        ]);

        $expired = $this->createPackage([
            'package_no' => 'PUBLIC-EXPIRED-001',
            'title' => 'Expired Public Umrah Package',
            'start_date' => today()->subDays(10)->toDateString(),
            'end_date' => today()->subDay()->toDateString(),
        ]);

        $this->get(route('website.umrah'))
            ->assertOk()
            ->assertSee($future->title)
            ->assertDontSee($expired->title);
    }

    public function test_expired_package_detail_returns_not_found(): void
    {
        $expired = $this->createPackage([
            'package_no' => 'PUBLIC-EXPIRED-DETAIL-001',
            'title' => 'Expired Detail Umrah Package',
            'start_date' => today()->subDays(10)->toDateString(),
            'end_date' => today()->subDay()->toDateString(),
        ]);

        $this->get(route('website.umrah.show', [
            'umrahPackage' => $expired,
        ]))->assertNotFound();
    }

    public function test_unit_only_distance_is_not_displayed_publicly(): void
    {
        $package = $this->createPackage([
            'package_no' => 'PUBLIC-DISTANCE-001',
            'title' => 'Public Distance Fallback Package',
            'makkah_distance' => '800 MTR',
            'madina_distance' => 'METER',
            'start_date' => today()->addMonth()->toDateString(),
            'end_date' => today()->addMonth()->addDays(10)->toDateString(),
        ]);

        $this->assertSame('800 MTR', $package->makkah_distance_display);
        $this->assertNull($package->madina_distance_display);

        $this->get(route('website.umrah.show', [
            'umrahPackage' => $package,
        ]))
            ->assertOk()
            ->assertSee('800 MTR')
            ->assertDontSee('>METER<', false);
    }

    public function test_quick_services_contains_travel_insurance_link(): void
    {
        $this->get(route('website.tickets'))
            ->assertOk()
            ->assertSee(route('website.insurance'), false)
            ->assertSee(__('website.travel_insurance'));
    }

    private function createPackage(array $overrides): UmrahPackage
    {
        return UmrahPackage::query()->create(array_merge([
            'package_no' => 'PUBLIC-BASE-001',
            'title' => 'Public Base Umrah Package',
            'status' => 'Available',
            'sharing_price' => 125000,
            'price' => 125000,
            'is_featured' => false,
            'sort_order' => 1,
        ], $overrides));
    }
}
