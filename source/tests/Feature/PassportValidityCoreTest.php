<?php

namespace Tests\Feature;

use App\Models\VisaApplication;
use App\Observers\VisaApplicationObserver;
use App\Services\PassportValidityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PassportValidityCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_passport_validity_columns_exist(): void
    {
        foreach (
            [
                'passport_expiry_date',
                'required_passport_validity_months',
                'passport_required_until',
                'passport_validity_status',
                'passport_validity_checked_at',
            ] as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'visa_applications',
                    $column,
                ),
                "Missing column: {$column}",
            );
        }
    }

    public function test_exact_six_month_boundary_is_valid(): void
    {
        $result = $this->service()->evaluate(
            passportExpiry: '2027-07-15',
            travelDate: '2027-01-01',
            returnDate: '2027-01-15',
            requiredMonths: 6,
            today: '2026-07-20',
        );

        $this->assertSame(
            PassportValidityService::STATUS_VALID,
            $result['status'],
        );

        $this->assertTrue(
            $result['is_valid_for_travel'],
        );

        $this->assertSame(
            '2027-07-15',
            $result['required_until']
                ?->toDateString(),
        );
    }

    public function test_one_day_short_requires_renewal(): void
    {
        $result = $this->service()->evaluate(
            passportExpiry: '2027-07-14',
            travelDate: '2027-01-01',
            returnDate: '2027-01-15',
            requiredMonths: 6,
            today: '2026-07-20',
        );

        $this->assertSame(
            PassportValidityService
                ::STATUS_RENEWAL_REQUIRED,
            $result['status'],
        );

        $this->assertFalse(
            $result['is_valid_for_travel'],
        );
    }

    public function test_return_date_is_used_for_rule(): void
    {
        $result = $this->service()->evaluate(
            passportExpiry: '2027-07-05',
            travelDate: '2027-01-01',
            returnDate: '2027-01-10',
            requiredMonths: 6,
            today: '2026-07-20',
        );

        $this->assertSame(
            '2027-07-10',
            $result['required_until']
                ?->toDateString(),
        );

        $this->assertSame(
            PassportValidityService
                ::STATUS_RENEWAL_REQUIRED,
            $result['status'],
        );
    }

    public function test_passport_expiring_before_return_is_invalid(): void
    {
        $result = $this->service()->evaluate(
            passportExpiry: '2027-01-05',
            travelDate: '2027-01-01',
            returnDate: '2027-01-10',
            requiredMonths: 6,
            today: '2026-07-20',
        );

        $this->assertSame(
            PassportValidityService
                ::STATUS_NOT_VALID_FOR_JOURNEY,
            $result['status'],
        );
    }

    public function test_expired_passport_is_detected(): void
    {
        $result = $this->service()->evaluate(
            passportExpiry: '2026-07-19',
            travelDate: '2026-08-01',
            returnDate: null,
            requiredMonths: 6,
            today: '2026-07-20',
        );

        $this->assertSame(
            PassportValidityService
                ::STATUS_EXPIRED,
            $result['status'],
        );
    }

    public function test_missing_values_are_reported(): void
    {
        $missingExpiry = $this->service()
            ->evaluate(
                passportExpiry: null,
                travelDate: '2026-10-01',
            );

        $this->assertSame(
            PassportValidityService
                ::STATUS_MISSING_EXPIRY,
            $missingExpiry['status'],
        );

        $missingTravel = $this->service()
            ->evaluate(
                passportExpiry: '2028-01-01',
                travelDate: null,
            );

        $this->assertSame(
            PassportValidityService
                ::STATUS_MISSING_TRAVEL_DATE,
            $missingTravel['status'],
        );
    }

    public function test_observer_stores_default_six_month_result(): void
    {
        $application =
            new VisaApplication();

        $application->forceFill([
            'passport_expiry_date' =>
                '2027-07-14',
            'travel_date' =>
                '2027-01-01',
            'return_date' =>
                '2027-01-15',
            'required_passport_validity_months' =>
                null,
        ]);

        app(
            VisaApplicationObserver::class,
        )->saving($application);

        $this->assertSame(
            6,
            $application
                ->required_passport_validity_months,
        );

        $this->assertSame(
            PassportValidityService
                ::STATUS_RENEWAL_REQUIRED,
            $application
                ->passport_validity_status,
        );

        $this->assertSame(
            '2027-07-15',
            $application
                ->passport_required_until
                ?->toDateString(),
        );

        $this->assertNotNull(
            $application
                ->passport_validity_checked_at,
        );
    }

    public function test_model_casts_passport_validity_dates(): void
    {
        $application =
            new VisaApplication();

        $casts = $application->getCasts();

        $this->assertSame(
            'date',
            $casts['passport_expiry_date'],
        );

        $this->assertSame(
            'integer',
            $casts[
                'required_passport_validity_months'
            ],
        );

        $this->assertSame(
            'date',
            $casts['passport_required_until'],
        );

        $this->assertSame(
            'datetime',
            $casts[
                'passport_validity_checked_at'
            ],
        );
    }

    private function service(): PassportValidityService
    {
        return app(
            PassportValidityService::class,
        );
    }
}
