<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use App\Services\TravelInsuranceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TravelInsuranceReportTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence =
        0;

    public function test_report_routes_and_permissions_are_registered(): void
    {
        $this->assertTrue(
            Route::has(
                'filament.admin.pages.travel-insurance-report',
            ),
        );

        $this->assertTrue(
            Route::has(
                'travel-insurance-report.export',
            ),
        );

        $route =
            Route::getRoutes()
                ->getByName(
                    'travel-insurance-report.export',
                );

        $this->assertNotNull(
            $route,
        );

        $this->assertContains(
            'auth',
            $route->gatherMiddleware(),
        );

        $options =
            \App\Filament\Resources\Users\Schemas\UserForm::getPermissionOptions();

        foreach (
            [
                'reports_view',
                'reports_print',
                'reports_pdf',
                'reports_excel',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );
        }
    }

    public function test_authorized_user_can_render_report_and_unauthorized_user_cannot(): void
    {
        $authorized =
            $this->reportUser(
                [
                    'reports_view',
                ],
            );

        $unauthorized =
            $this->reportUser(
                [],
            );

        $this->actingAs(
            $authorized,
        )
            ->get(
                route(
                    'filament.admin.pages.travel-insurance-report',
                ),
            )
            ->assertOk()
            ->assertSee(
                'Travel Insurance Report',
            );

        $this->actingAs(
            $unauthorized,
        )
            ->get(
                route(
                    'filament.admin.pages.travel-insurance-report',
                ),
            )
            ->assertForbidden();
    }

    public function test_service_filters_and_summarizes_report_rows(): void
    {
        $providerOne =
            $this->provider(
                'Filter Provider One',
            );

        $providerTwo =
            $this->provider(
                'Filter Provider Two',
            );

        $included =
            $this->policy(
                $providerOne,
                [
                    'traveller_name' =>
                        'Included Report Traveller',

                    'policy_start_date' =>
                        today()->subDays(
                            5,
                        ),

                    'status' =>
                        'Active',

                    'approval_status' =>
                        'Approved',
                ],
            );

        $this->policy(
            $providerTwo,
            [
                'traveller_name' =>
                    'Excluded Report Traveller',

                'policy_start_date' =>
                    today()->subDays(
                        5,
                    ),

                'status' =>
                    'Expired',

                'approval_status' =>
                    'Approved',
            ],
        );

        $service =
            app(
                TravelInsuranceReportService::class,
            );

        $query =
            $service->query(
                [
                    'provider_id' =>
                        $providerOne->id,

                    'status' =>
                        'Active',

                    'approval_status' =>
                        'Approved',

                    'date_from' =>
                        today()->subDays(
                            10,
                        )->toDateString(),

                    'date_to' =>
                        today()->toDateString(),
                ],
            );

        $summary =
            $service->summary(
                clone $query,
            );

        $this->assertSame(
            1,
            $query->count(),
        );

        $this->assertSame(
            1,
            $summary['policy_count'],
        );

        $this->assertSame(
            1,
            $summary['active_count'],
        );

        $this->assertSame(
            round(
                (float) $included->fresh()->total_amount,
                2,
            ),
            $summary['total_amount'],
        );
    }

    public function test_page_applies_filters_and_hides_raw_storage_paths(): void
    {
        $user =
            $this->reportUser(
                [
                    'reports_view',
                    'reports_print',
                    'reports_pdf',
                    'reports_excel',
                ],
            );

        $provider =
            $this->provider(
                'Visible Report Provider',
            );

        $visible =
            $this->policy(
                $provider,
                [
                    'traveller_name' =>
                        'Visible Report Traveller',

                    'policy_document_path' =>
                        'travel-insurance/policies/private-report.pdf',
                ],
            );

        $otherProvider =
            $this->provider(
                'Hidden Report Provider',
            );

        $hidden =
            $this->policy(
                $otherProvider,
                [
                    'traveller_name' =>
                        'Hidden Report Traveller',
                ],
            );

        $this->actingAs(
            $user,
        )
            ->get(
                route(
                    'filament.admin.pages.travel-insurance-report',
                    [
                        'provider_id' =>
                            $provider->id,
                    ],
                ),
            )
            ->assertOk()
            ->assertSee(
                $visible->policy_no,
            )
            ->assertSee(
                'Visible Report Traveller',
            )
            ->assertDontSee(
                $hidden->policy_no,
            )
            ->assertDontSee(
                'Hidden Report Traveller',
            )
            ->assertDontSee(
                'travel-insurance/policies/private-report.pdf',
            )
            ->assertDontSee(
                'policy_document_path',
            );
    }

    public function test_print_export_respects_filters_and_permission(): void
    {
        $provider =
            $this->provider(
                'Print Export Provider',
            );

        $policy =
            $this->policy(
                $provider,
                [
                    'traveller_name' =>
                        'Print Export Traveller',
                ],
            );

        $allowed =
            $this->reportUser(
                [
                    'reports_view',
                    'reports_print',
                ],
            );

        $denied =
            $this->reportUser(
                [
                    'reports_view',
                ],
            );

        $url =
            route(
                'travel-insurance-report.export',
                [
                    'format' =>
                        'print',

                    'provider_id' =>
                        $provider->id,
                ],
            );

        $this->actingAs(
            $allowed,
        )
            ->get(
                $url,
            )
            ->assertOk()
            ->assertSee(
                $policy->policy_no,
            )
            ->assertSee(
                'window.print()',
                false,
            )
            ->assertHeader(
                'x-content-type-options',
                'nosniff',
            );

        $this->actingAs(
            $denied,
        )
            ->get(
                $url,
            )
            ->assertForbidden();
    }

    public function test_pdf_export_returns_pdf_and_requires_pdf_permission(): void
    {
        $provider =
            $this->provider(
                'PDF Export Provider',
            );

        $this->policy(
            $provider,
        );

        $allowed =
            $this->reportUser(
                [
                    'reports_view',
                    'reports_pdf',
                ],
            );

        $denied =
            $this->reportUser(
                [
                    'reports_view',
                ],
            );

        $url =
            route(
                'travel-insurance-report.export',
                [
                    'format' =>
                        'pdf',

                    'provider_id' =>
                        $provider->id,
                ],
            );

        $response =
            $this->actingAs(
                $allowed,
            )->get(
                $url,
            );

        $response
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/pdf',
            );

        $this->assertStringStartsWith(
            '%PDF',
            (string) $response->getContent(),
        );

        $this->actingAs(
            $denied,
        )
            ->get(
                $url,
            )
            ->assertForbidden();
    }

    public function test_excel_export_is_excel_compatible_and_formula_safe(): void
    {
        $provider =
            $this->provider(
                'Excel Export Provider',
            );

        $policy =
            $this->policy(
                $provider,
                [
                    'traveller_name' =>
                        '=HYPERLINK("https://example.test","Unsafe")',
                ],
            );

        $user =
            $this->reportUser(
                [
                    'reports_view',
                    'reports_excel',
                ],
            );

        $response =
            $this->actingAs(
                $user,
            )
                ->get(
                    route(
                        'travel-insurance-report.export',
                        [
                            'format' =>
                                'excel',

                            'provider_id' =>
                                $provider->id,
                        ],
                    ),
                );

        $response
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.ms-excel; charset=UTF-8',
            )
            ->assertHeader(
                'x-content-type-options',
                'nosniff',
            );

        $content =
            $response->streamedContent();

        $this->assertStringContainsString(
            'Policy No',
            $content,
        );

        $this->assertStringContainsString(
            $policy->policy_no,
            $content,
        );

        $this->assertStringContainsString(
            "'=HYPERLINK",
            $content,
        );

        $this->assertStringNotContainsString(
            'policy_document_path',
            $content,
        );
    }

    public function test_invalid_format_and_missing_export_permission_are_rejected(): void
    {
        $user =
            $this->reportUser(
                [
                    'reports_view',
                ],
            );

        $this->actingAs(
            $user,
        )
            ->get(
                '/travel-insurance-report/json',
            )
            ->assertNotFound();

        foreach (
            [
                'print',
                'pdf',
                'excel',
            ]
            as $format
        ) {
            $this->actingAs(
                $user,
            )
                ->get(
                    route(
                        'travel-insurance-report.export',
                        [
                            'format' =>
                                $format,
                        ],
                    ),
                )
                ->assertForbidden();
        }
    }

    private function reportUser(
        array $permissions,
    ): User {
        return User::factory()->create(
            [
                'role' =>
                    'Accountant',

                'is_active' =>
                    true,

                'permissions' =>
                    $permissions,
            ],
        );
    }

    private function provider(
        string $name,
    ): InsuranceProvider {
        self::$sequence++;

        return InsuranceProvider::query()->create(
            [
                'name' =>
                    $name
                    .' '
                    .self::$sequence,

                'country' =>
                    'Pakistan',

                'status' =>
                    'Active',
            ],
        );
    }

    private function policy(
        InsuranceProvider $provider,
        array $attributes = [],
    ): TravelInsurancePolicy {
        self::$sequence++;

        $sequence =
            self::$sequence;

        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        "Report Customer {$sequence}",

                    'mobile' =>
                        '0302'
                        .str_pad(
                            (string) $sequence,
                            7,
                            '0',
                            STR_PAD_LEFT,
                        ),

                    'email' =>
                        "report-customer-{$sequence}@example.test",

                    'customer_type' =>
                        'Agent',

                    'status' =>
                        'Active',
                ],
            );

        return TravelInsurancePolicy::query()->create(
            array_merge(
                [
                    'policy_no' =>
                        "TIP-REPORT-{$sequence}",

                    'customer_id' =>
                        $customer->id,

                    'insurance_provider_id' =>
                        $provider->id,

                    'traveller_name' =>
                        "Report Traveller {$sequence}",

                    'passport_no' =>
                        "REPORT{$sequence}",

                    'destination_country' =>
                        'Saudi Arabia',

                    'trip_type' =>
                        'Single Trip',

                    'coverage_type' =>
                        'Comprehensive',

                    'coverage_amount' =>
                        50000,

                    'coverage_currency' =>
                        'USD',

                    'travel_start_date' =>
                        today()->addDays(
                            10,
                        ),

                    'travel_end_date' =>
                        today()->addDays(
                            20,
                        ),

                    'policy_start_date' =>
                        today(),

                    'policy_end_date' =>
                        today()->addDays(
                            20,
                        ),

                    'premium_amount' =>
                        10000,

                    'supplier_cost' =>
                        7000,

                    'discount' =>
                        500,

                    'tax' =>
                        250,

                    'currency' =>
                        'PKR',

                    'status' =>
                        'Active',

                    'approval_status' =>
                        'Approved',
                ],
                $attributes,
            ),
        );
    }
}