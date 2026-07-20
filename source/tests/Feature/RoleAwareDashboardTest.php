<?php

namespace Tests\Feature;

use App\Filament\CustomPages\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class RoleAwareDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_staff_does_not_receive_executive_finance_data(): void
    {
        $user = User::factory()->create([
            'role' => 'Sales Staff',
            'permissions' => [
                'customers_view',
                'visa_applications_view',
                'visa_payments_view',
            ],
        ]);

        $this->actingAs($user);

        $data = $this->dashboardData();

        $labels = collect($data['stats'])
            ->pluck('label')
            ->all();

        $this->assertFalse(
            $data['showExecutiveFinance'],
        );

        $this->assertContains(
            'Total Customers',
            $labels,
        );

        $this->assertContains(
            'Visa Applications',
            $labels,
        );

        $this->assertNotContains(
            'Total Sales',
            $labels,
        );

        $this->assertNotContains(
            'Total Expenses',
            $labels,
        );

        $this->assertFalse(
            $data['showRecentPayments'],
        );

        $this->assertTrue(
            $data['recentPayments']->isEmpty(),
        );
    }

    public function test_accountant_receives_real_zero_finance_metrics(): void
    {
        $user = User::factory()->create([
            'role' => 'Accountant',
            'permissions' => [
                'reports_view',
                'expenses_view',
                'visa_payments_view',
                'flight_payments_view',
                'umrah_payments_view',
                'hotel_payments_view',
            ],
        ]);

        $this->actingAs($user);

        $data = $this->dashboardData();

        $labels = collect($data['stats'])
            ->pluck('label')
            ->all();

        $this->assertTrue(
            $data['showExecutiveFinance'],
        );

        $this->assertContains(
            'Total Sales',
            $labels,
        );

        $this->assertContains(
            'Total Expenses',
            $labels,
        );

        $this->assertSame(
            'PKR 0.00',
            $data['totalRevenue'],
        );

        $this->assertSame(
            'No activity this month',
            $data['revenueTrend'],
        );

        $this->assertFalse(
            $data['chart']['has_data'],
        );

        $this->assertSame(
            [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
            $data['chart']['values'],
        );
    }

    public function test_dashboard_identity_and_dates_are_dynamic(): void
    {
        $user = User::factory()->create([
            'name' => 'M Sohaib Aslam',
            'role' => 'Accountant',
            'permissions' => [
                'reports_view',
            ],
        ]);

        $this->actingAs($user);

        $data = $this->dashboardData();

        $this->assertSame(
            'M Sohaib Aslam',
            $data['currentUserName'],
        );

        $this->assertSame(
            'Accountant',
            $data['currentUserRole'],
        );

        $this->assertSame(
            'M',
            $data['currentUserInitial'],
        );

        $this->assertSame(
            now('Asia/Karachi')
                ->subDays(6)
                ->format('d M'),
            $data['chart']['labels'][0],
        );

        $this->assertSame(
            now('Asia/Karachi')
                ->format('d M'),
            $data['chart']['labels'][6],
        );
    }

    public function test_dashboard_contains_no_demo_financial_values(): void
    {
        $source = file_get_contents(
            app_path(
                'Filament/CustomPages/Dashboard.php',
            ),
        );

        $view = file_get_contents(
            resource_path(
                'views/filament/pages/dashboard.blade.php',
            ),
        );

        $this->assertStringNotContainsString(
            '120000',
            $source,
        );

        $this->assertStringNotContainsString(
            '1250000',
            $source,
        );

        $this->assertStringNotContainsString(
            '18.7%',
            $view,
        );

        $this->assertStringNotContainsString(
            '1 May',
            $source,
        );

        $this->assertStringNotContainsString(
            'Welcome back, Admin',
            $view,
        );
    }

    public function test_dashboard_keeps_task_and_passport_components(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/filament/pages/dashboard.blade.php',
            ),
        );

        $this->assertStringContainsString(
            '<x-admin-task-dashboard />',
            $view,
        );

        $this->assertStringContainsString(
            '<x-admin-passport-dashboard />',
            $view,
        );
    }

    private function dashboardData(): array
    {
        $dashboard = app(
            Dashboard::class,
        );

        $method = new ReflectionMethod(
            $dashboard,
            'getViewData',
        );

        $method->setAccessible(true);

        return $method->invoke(
            $dashboard,
        );
    }
}