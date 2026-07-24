<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\PassportValidityReport;
use App\Models\User;
use App\View\Components\AdminPassportDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPassportPrivacyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_manager_cannot_view_passport_report_or_widget(): void
    {
        $user = User::factory()->create([
            'role' => 'Manager',
            'is_active' => true,
            'permissions' => [
                'pakistan_tours_view',
                'gallery_view',
                'testimonials_view',
                'faqs_view',
                'blog_posts_view',
                'promotions_view',
            ],
        ]);

        $this->actingAs($user);

        $this->assertFalse(
            PassportValidityReport::canAccess(),
        );

        $this->assertFalse(
            (new AdminPassportDashboard)->canView,
        );
    }

    public function test_reports_only_manager_cannot_view_passport_report_or_widget(): void
    {
        $user = User::factory()->create([
            'role' => 'Manager',
            'is_active' => true,
            'permissions' => [
                'reports_view',
            ],
        ]);

        $this->actingAs($user);

        $this->assertFalse(
            PassportValidityReport::canAccess(),
        );

        $this->assertFalse(
            (new AdminPassportDashboard)->canView,
        );
    }

    public function test_active_visa_manager_can_view_passport_report_and_widget(): void
    {
        $user = User::factory()->create([
            'role' => 'Manager',
            'is_active' => true,
            'permissions' => [
                'visa_applications_view',
            ],
        ]);

        $this->actingAs($user);

        $this->assertTrue(
            PassportValidityReport::canAccess(),
        );

        $this->assertTrue(
            (new AdminPassportDashboard)->canView,
        );
    }

    public function test_inactive_visa_manager_cannot_view_passport_report_or_widget(): void
    {
        $user = User::factory()->create([
            'role' => 'Manager',
            'is_active' => false,
            'permissions' => [
                'visa_applications_view',
            ],
        ]);

        $this->actingAs($user);

        $this->assertFalse(
            PassportValidityReport::canAccess(),
        );

        $this->assertFalse(
            (new AdminPassportDashboard)->canView,
        );
    }
}
