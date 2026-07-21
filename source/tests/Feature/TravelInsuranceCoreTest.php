<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TravelInsuranceCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_insurance_tables_and_core_columns_exist(): void
    {
        $this->assertTrue(
            Schema::hasTable(
                'insurance_providers',
            ),
        );

        $this->assertTrue(
            Schema::hasTable(
                'travel_insurance_policies',
            ),
        );

        $this->assertTrue(
            Schema::hasTable(
                'travel_insurance_payments',
            ),
        );

        $this->assertTrue(
            Schema::hasColumns(
                'travel_insurance_policies',
                [
                    'policy_no',
                    'customer_id',
                    'insurance_provider_id',
                    'invoice_id',
                    'traveller_name',
                    'passport_no',
                    'destination_country',
                    'coverage_amount',
                    'premium_amount',
                    'supplier_cost',
                    'total_amount',
                    'paid_amount',
                    'balance_amount',
                    'profit_amount',
                    'payment_status',
                    'approval_status',
                    'policy_document_path',
                ],
            ),
        );

        $this->assertTrue(
            Schema::hasColumns(
                'travel_insurance_payments',
                [
                    'payment_no',
                    'travel_insurance_policy_id',
                    'account_voucher_id',
                    'amount',
                    'status',
                    'posting_status',
                    'receipt_path',
                ],
            ),
        );
    }

    public function test_policy_generates_number_and_calculates_financials(): void
    {
        $policy =
            $this->createPolicy();

        $this->assertStringStartsWith(
            'TI-',
            $policy->policy_no,
        );

        $this->assertSame(
            '9750.00',
            $policy->total_amount,
        );

        $this->assertSame(
            '2750.00',
            $policy->profit_amount,
        );

        $this->assertSame(
            '0.00',
            $policy->paid_amount,
        );

        $this->assertSame(
            '9750.00',
            $policy->balance_amount,
        );

        $this->assertSame(
            'Unpaid',
            $policy->payment_status,
        );
    }

    public function test_only_approved_payments_update_policy_balance(): void
    {
        $policy =
            $this->createPolicy();

        $pendingPayment =
            TravelInsurancePayment::query()->create(
                [
                    'travel_insurance_policy_id' =>
                        $policy->id,

                    'payment_date' =>
                        today(),

                    'amount' =>
                        1500,

                    'payment_method' =>
                        'Cash',

                    'status' =>
                        'Pending',
                ],
            );

        $this->assertStringStartsWith(
            'TIP-',
            $pendingPayment->payment_no,
        );

        $policy->refresh();

        $this->assertSame(
            '0.00',
            $policy->paid_amount,
        );

        $this->assertSame(
            'Unpaid',
            $policy->payment_status,
        );

        $pendingPayment->update(
            [
                'status' =>
                    'Approved',
            ],
        );

        $policy->refresh();

        $this->assertSame(
            '1500.00',
            $policy->paid_amount,
        );

        $this->assertSame(
            '8250.00',
            $policy->balance_amount,
        );

        $this->assertSame(
            'Partial',
            $policy->payment_status,
        );
    }

    public function test_full_approved_payment_marks_policy_paid(): void
    {
        $policy =
            $this->createPolicy();

        TravelInsurancePayment::query()->create(
            [
                'travel_insurance_policy_id' =>
                    $policy->id,

                'payment_date' =>
                    today(),

                'amount' =>
                    9750,

                'payment_method' =>
                    'Bank Transfer',

                'reference_no' =>
                    'BANK-TEST-001',

                'status' =>
                    'Approved',
            ],
        );

        $policy->refresh();

        $this->assertSame(
            '9750.00',
            $policy->paid_amount,
        );

        $this->assertSame(
            '0.00',
            $policy->balance_amount,
        );

        $this->assertSame(
            'Paid',
            $policy->payment_status,
        );
    }

    public function test_provider_and_customer_relationships_are_available(): void
    {
        $policy =
            $this->createPolicy();

        $this->assertInstanceOf(
            Customer::class,
            $policy->customer,
        );

        $this->assertInstanceOf(
            InsuranceProvider::class,
            $policy->provider,
        );

        $this->assertSame(
            'Insurance Test Provider',
            $policy->provider->name,
        );

        $this->assertSame(
            $policy->id,
            $policy->provider
                ->policies()
                ->firstOrFail()
                ->id,
        );
    }

    private function createPolicy(): TravelInsurancePolicy
    {
        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        'Insurance Test Customer',

                    'mobile' =>
                        '03000000000',

                    'customer_type' =>
                        'Agent',

                    'status' =>
                        'Active',
                ],
            );

        $provider =
            InsuranceProvider::query()->create(
                [
                    'name' =>
                        'Insurance Test Provider',

                    'company_name' =>
                        'Insurance Test Provider',

                    'country' =>
                        'Pakistan',

                    'status' =>
                        'Active',
                ],
            );

        return TravelInsurancePolicy::query()->create(
            [
                'customer_id' =>
                    $customer->id,

                'insurance_provider_id' =>
                    $provider->id,

                'traveller_name' =>
                    $customer->name,

                'passport_no' =>
                    'AB1234567',

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
                        25,
                    ),

                'policy_start_date' =>
                    today()->addDays(
                        10,
                    ),

                'policy_end_date' =>
                    today()->addDays(
                        25,
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
                    'Draft',

                'approval_status' =>
                    'Pending',
            ],
        );
    }
}