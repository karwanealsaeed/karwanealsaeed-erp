<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PassportDashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PassportDashboardReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            '2026-07-20 10:00:00',
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_report_route_is_registered(): void
    {
        $this->assertTrue(
            Route::has(
                'filament.admin.pages.'
                .'passport-validity-report',
            ),
        );
    }

    public function test_dashboard_contains_passport_component(): void
    {
        $dashboard = file_get_contents(
            resource_path(
                'views/filament/pages/'
                .'dashboard.blade.php',
            ),
        );

        $this->assertStringContainsString(
            '<x-admin-passport-dashboard />',
            $dashboard,
        );
    }

    public function test_empty_summary_returns_zero_counts(): void
    {
        $summary = app(
            PassportDashboardService::class,
        )->summary();

        $this->assertSame(
            0,
            $summary['total'],
        );

        $this->assertSame(
            0,
            $summary['action_required'],
        );

        $this->assertSame(
            0,
            $summary['expiring_soon'],
        );
    }

    public function test_summary_and_filters_classify_passports(): void
    {
        $this->application([
            'application_no' =>
                'VA-EXPIRED',

            'passport_validity_status' =>
                'Expired',

            'passport_expiry_date' =>
                '2026-07-01',
        ]);

        $this->application([
            'application_no' =>
                'VA-RENEWAL',

            'passport_validity_status' =>
                'Renewal Required',

            'passport_expiry_date' =>
                '2026-12-01',
        ]);

        $this->application([
            'application_no' =>
                'VA-JOURNEY',

            'passport_validity_status' =>
                'Not Valid for Journey',

            'passport_expiry_date' =>
                '2026-08-05',
        ]);

        $this->application([
            'application_no' =>
                'VA-SOON',

            'passport_validity_status' =>
                'Valid',

            'passport_expiry_date' =>
                '2026-11-01',
        ]);

        $service = app(
            PassportDashboardService::class,
        );

        $summary = $service->summary();

        $this->assertSame(
            4,
            $summary['total'],
        );

        $this->assertSame(
            4,
            $summary['action_required'],
        );

        $this->assertSame(
            1,
            $summary['expired'],
        );

        $this->assertSame(
            1,
            $summary['renewal_required'],
        );

        $this->assertSame(
            1,
            $summary['journey_invalid'],
        );

        $this->assertSame(
            1,
            $summary['expiring_soon'],
        );

        $this->assertCount(
            4,
            $service->actionRows(),
        );

        $this->assertCount(
            1,
            $service->reportRows(
                statusFilter: 'Expired',
            ),
        );

        $this->assertCount(
            1,
            $service->reportRows(
                statusFilter:
                    'Passport Expiring Soon',
            ),
        );
    }

    public function test_authorized_visa_staff_can_render_report_and_dashboard(): void
    {
        $user = User::factory()->create([
            'role' =>
                'Sales Staff',

            'is_active' =>
                true,

            'permissions' => [
                'visa_applications_view',
            ],
        ]);

        $this
            ->actingAs($user)
            ->get(
                route(
                    'filament.admin.pages.'
                    .'passport-validity-report',
                ),
            )
            ->assertOk()
            ->assertSeeText(
                'Passport Validity Report',
            )
            ->assertSeeText(
                'Print / Save PDF',
            );

        $this
            ->actingAs($user)
            ->get(
                route(
                    'filament.admin.pages.dashboard',
                ),
            )
            ->assertOk()
            ->assertSeeText(
                'Passport Validity Control',
            );
    }

    public function test_unauthorized_staff_cannot_open_report(): void
    {
        $user = User::factory()->create([
            'role' =>
                'Accountant',

            'is_active' =>
                true,

            'permissions' =>
                [],
        ]);

        $this
            ->actingAs($user)
            ->get(
                route(
                    'filament.admin.pages.'
                    .'passport-validity-report',
                ),
            )
            ->assertForbidden();
    }

    public function test_report_is_print_and_pdf_ready(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/filament/pages/'
                .'passport-validity-report.blade.php',
            ),
        );

        $this->assertStringContainsString(
            'window.print()',
            $view,
        );

        $this->assertStringContainsString(
            '@media print',
            $view,
        );

        $this->assertStringContainsString(
            'Save PDF',
            $view,
        );
    }

    private function application(
        array $overrides = [],
    ): int {
        $token = strtoupper(
            substr(
                hash(
                    'sha256',
                    uniqid('', true),
                ),
                0,
                10,
            ),
        );

        $countryId =
            $this->insertRequiredRow(
                'countries',
                [
                    'name' =>
                        'Country '.$token,

                    'country_name' =>
                        'Country '.$token,

                    'code' =>
                        substr($token, 0, 3),

                    'status' =>
                        'Active',
                ],
            );

        $visaTypeId =
            $this->insertRequiredRow(
                'visa_types',
                [
                    'name' =>
                        'Visa '.$token,

                    'title' =>
                        'Visa '.$token,

                    'country_id' =>
                        $countryId,

                    'status' =>
                        'Active',
                ],
            );

        $customerId =
            $this->insertRequiredRow(
                'customers',
                [
                    'name' =>
                        'Customer '.$token,

                    'mobile' =>
                        '0300'
                        .substr(
                            preg_replace(
                                '/\D/',
                                '',
                                hexdec(
                                    substr(
                                        $token,
                                        0,
                                        7,
                                    ),
                                ),
                            )
                            .'0000000',
                            0,
                            7,
                        ),

                    'passport_no' =>
                        'P-'.$token,

                    'status' =>
                        'Active',
                ],
            );

        $attributes = array_merge(
            [
                'application_no' =>
                    'VA-'.$token,

                'customer_id' =>
                    $customerId,

                'country_id' =>
                    $countryId,

                'visa_type_id' =>
                    $visaTypeId,

                'passport_no' =>
                    'P-'.$token,

                'passport_expiry_date' =>
                    '2027-12-31',

                'required_passport_validity_months' =>
                    6,

                'passport_required_until' =>
                    '2027-02-10',

                'passport_validity_status' =>
                    'Valid',

                'passport_validity_checked_at' =>
                    now(),

                'travel_date' =>
                    '2026-08-01',

                'return_date' =>
                    '2026-08-10',

                'status' =>
                    'Applied',
            ],
            $overrides,
        );

        return $this->insertRequiredRow(
            'visa_applications',
            $attributes,
        );
    }

    private function insertRequiredRow(
        string $table,
        array $overrides,
    ): int {
        $columns =
            Schema::getColumns($table);

        $columnNames = collect($columns)
            ->pluck('name')
            ->all();

        $data = [];

        foreach (
            $overrides
            as $name => $value
        ) {
            if (
                in_array(
                    $name,
                    $columnNames,
                    true,
                )
            ) {
                $data[$name] = $value;
            }
        }

        $token = hash(
            'sha256',
            $table
            .uniqid('', true),
        );

        foreach ($columns as $column) {
            $name = $column['name'];

            if (
                array_key_exists(
                    $name,
                    $data,
                )
            ) {
                continue;
            }

            if (
                in_array(
                    $name,
                    [
                        'id',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    true,
                )
            ) {
                continue;
            }

            if (
                ($column[
                    'auto_increment'
                ] ?? false) === true
            ) {
                continue;
            }

            if (
                ($column['nullable'] ?? false)
                === true
            ) {
                continue;
            }

            if (
                array_key_exists(
                    'default',
                    $column,
                )
                && $column['default'] !== null
            ) {
                continue;
            }

            $type = strtolower(
                (string) (
                    $column['type_name']
                    ?? $column['type']
                    ?? 'string'
                ),
            );

            $data[$name] =
                $this->defaultColumnValue(
                    $name,
                    $type,
                    $token,
                );
        }

        if (
            in_array(
                'created_at',
                $columnNames,
                true,
            )
        ) {
            $data['created_at'] =
                now();
        }

        if (
            in_array(
                'updated_at',
                $columnNames,
                true,
            )
        ) {
            $data['updated_at'] =
                now();
        }

        return (int) DB::table($table)
            ->insertGetId($data);
    }

    private function defaultColumnValue(
        string $name,
        string $type,
        string $token,
    ): mixed {
        if (
            str_contains($type, 'int')
            || str_contains($type, 'decimal')
            || str_contains($type, 'numeric')
            || str_contains($type, 'float')
            || str_contains($type, 'double')
            || str_contains($type, 'real')
        ) {
            return 0;
        }

        if (
            str_contains($type, 'bool')
        ) {
            return true;
        }

        if (
            str_contains($type, 'date')
            || str_contains($type, 'time')
        ) {
            return now();
        }

        if (
            str_contains($type, 'json')
        ) {
            return json_encode(
                [],
                JSON_THROW_ON_ERROR,
            );
        }

        if (
            str_contains($name, 'email')
        ) {
            return substr(
                $token,
                0,
                12,
            )
            .'@example.test';
        }

        if (
            str_contains($name, 'mobile')
            || str_contains($name, 'phone')
        ) {
            return '0300'
                .substr(
                    preg_replace(
                        '/\D/',
                        '',
                        hexdec(
                            substr(
                                $token,
                                0,
                                7,
                            ),
                        ),
                    )
                    .'0000000',
                    0,
                    7,
                );
        }

        return strtoupper(
            substr(
                $token,
                0,
                12,
            ),
        );
    }
}
