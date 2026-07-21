<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\AccountHead;
use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\Invoice;
use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use App\Services\TravelInsuranceInvoiceService;
use App\Services\TravelInsurancePaymentApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TravelInsuranceInvoiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_and_admin_ui_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        $adminDefaults =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        $this->assertArrayHasKey(
            'travel_insurance_policies_invoice',
            $options,
        );

        $this->assertContains(
            'travel_insurance_policies_invoice',
            $adminDefaults,
        );

        $invoiceForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/Invoices/Schemas/InvoiceForm.php',
                ),
            );

        $policyView =
            file_get_contents(
                app_path(
                    'Filament/Resources/TravelInsurancePolicies/Pages/ViewTravelInsurancePolicy.php',
                ),
            );

        $policyTable =
            file_get_contents(
                app_path(
                    'Filament/Resources/TravelInsurancePolicies/Tables/TravelInsurancePoliciesTable.php',
                ),
            );

        $this->assertStringContainsString(
            "'Travel Insurance' => 'Travel Insurance'",
            $invoiceForm,
        );

        $this->assertStringContainsString(
            "'createOrSyncInvoice'",
            $policyView,
        );

        $this->assertStringContainsString(
            "'createOrSyncInvoice'",
            $policyTable,
        );

        $this->assertStringContainsString(
            "'travel_insurance_policies_invoice'",
            $policyView,
        );

        $this->assertStringContainsString(
            "'travel_insurance_policies_invoice'",
            $policyTable,
        );
    }

    public function test_service_creates_and_links_policy_invoice(): void
    {
        $policy =
            $this->createPolicy();

        $invoice =
            app(
                TravelInsuranceInvoiceService::class,
            )->createOrSync(
                $policy,
            );

        $policy->refresh();

        $this->assertSame(
            $invoice->id,
            $policy->invoice_id,
        );

        $this->assertSame(
            $policy->customer_id,
            $invoice->customer_id,
        );

        $this->assertSame(
            'Travel Insurance',
            $invoice->service_type,
        );

        $this->assertSame(
            $policy->policy_no,
            $invoice->service_reference,
        );

        $this->assertSame(
            10000.0,
            (float) $invoice->sub_total,
        );

        $this->assertSame(
            500.0,
            (float) $invoice->discount,
        );

        $this->assertSame(
            250.0,
            (float) $invoice->tax,
        );

        $this->assertSame(
            9750.0,
            (float) $invoice->total_amount,
        );

        $this->assertSame(
            0.0,
            (float) $invoice->paid_amount,
        );

        $this->assertSame(
            9750.0,
            (float) $invoice->balance_amount,
        );

        $this->assertSame(
            'Unpaid',
            $invoice->status,
        );

        $this->assertStringStartsWith(
            'INV-',
            $invoice->invoice_no,
        );

        $this->assertStringContainsString(
            $policy->policy_no,
            (string) $invoice->notes,
        );
    }

    public function test_repeated_sync_does_not_create_duplicate_invoice(): void
    {
        $policy =
            $this->createPolicy();

        $service =
            app(
                TravelInsuranceInvoiceService::class,
            );

        $first =
            $service->createOrSync(
                $policy,
            );

        $second =
            $service->createOrSync(
                $policy->fresh(),
            );

        $this->assertSame(
            $first->id,
            $second->id,
        );

        $this->assertSame(
            1,
            Invoice::query()
                ->where(
                    'service_type',
                    'Travel Insurance',
                )
                ->where(
                    'service_reference',
                    $policy->policy_no,
                )
                ->count(),
        );
    }

    public function test_matching_orphan_invoice_is_relinked(): void
    {
        $policy =
            $this->createPolicy();

        $orphan =
            Invoice::query()->create(
                [
                    'invoice_no' =>
                        'INV-09000',

                    'customer_id' =>
                        $policy->customer_id,

                    'invoice_date' =>
                        today(),

                    'due_date' =>
                        today(),

                    'service_type' =>
                        'Travel Insurance',

                    'service_reference' =>
                        $policy->policy_no,

                    'sub_total' =>
                        1,

                    'discount' =>
                        0,

                    'tax' =>
                        0,

                    'total_amount' =>
                        1,

                    'paid_amount' =>
                        0,

                    'balance_amount' =>
                        1,

                    'status' =>
                        'Draft',
                ],
            );

        $synced =
            app(
                TravelInsuranceInvoiceService::class,
            )->createOrSync(
                $policy,
            );

        $policy->refresh();

        $this->assertSame(
            $orphan->id,
            $synced->id,
        );

        $this->assertSame(
            $orphan->id,
            $policy->invoice_id,
        );

        $this->assertSame(
            1,
            Invoice::query()
                ->where(
                    'service_type',
                    'Travel Insurance',
                )
                ->where(
                    'service_reference',
                    $policy->policy_no,
                )
                ->count(),
        );

        $this->assertSame(
            9750.0,
            (float) $synced->total_amount,
        );
    }

    public function test_invoice_balances_and_status_follow_policy(): void
    {
        $policy =
            $this->createPolicy();

        $service =
            app(
                TravelInsuranceInvoiceService::class,
            );

        $invoice =
            $service->createOrSync(
                $policy,
            );

        $this->createApprovedFixturePayment(
            $policy,
            4000,
        );

        $invoice =
            $service->syncExisting(
                $policy->fresh(),
            );

        $this->assertNotNull(
            $invoice,
        );

        $this->assertSame(
            4000.0,
            (float) $invoice->paid_amount,
        );

        $this->assertSame(
            5750.0,
            (float) $invoice->balance_amount,
        );

        $this->assertSame(
            'Partially Paid',
            $invoice->status,
        );

        $this->createApprovedFixturePayment(
            $policy,
            5750,
        );

        $invoice =
            $service->syncExisting(
                $policy->fresh(),
            );

        $this->assertSame(
            9750.0,
            (float) $invoice->paid_amount,
        );

        $this->assertSame(
            0.0,
            (float) $invoice->balance_amount,
        );

        $this->assertSame(
            'Paid',
            $invoice->status,
        );

        $policy->update(
            [
                'status' =>
                    'Cancelled',
            ],
        );

        $invoice =
            $service->syncExisting(
                $policy->fresh(),
            );

        $this->assertSame(
            'Cancelled',
            $invoice->status,
        );
    }

    public function test_payment_approval_synchronizes_linked_invoice(): void
    {
        $this->ensureAccountingAccounts();

        $policy =
            $this->createPolicy();

        $invoice =
            app(
                TravelInsuranceInvoiceService::class,
            )->createOrSync(
                $policy,
            );

        $payment =
            TravelInsurancePayment::query()->create(
                [
                    'travel_insurance_policy_id' =>
                        $policy->id,

                    'payment_date' =>
                        today(),

                    'amount' =>
                        2000,

                    'payment_method' =>
                        'Cash',

                    'status' =>
                        'Pending',

                    'posting_status' =>
                        'Pending',
                ],
            );

        app(
            TravelInsurancePaymentApprovalService::class,
        )->approve(
            $payment,
            $this->createUser(),
        );

        $payment->refresh();
        $invoice->refresh();

        $this->assertSame(
            'Posted',
            $payment->posting_status,
        );

        $this->assertSame(
            2000.0,
            (float) $invoice->paid_amount,
        );

        $this->assertSame(
            7750.0,
            (float) $invoice->balance_amount,
        );

        $this->assertSame(
            'Partially Paid',
            $invoice->status,
        );
    }

    public function test_unrelated_linked_invoice_is_rejected(): void
    {
        $policy =
            $this->createPolicy();

        $unrelated =
            Invoice::query()->create(
                [
                    'invoice_no' =>
                        'INV-09999',

                    'customer_id' =>
                        $policy->customer_id,

                    'invoice_date' =>
                        today(),

                    'due_date' =>
                        today(),

                    'service_type' =>
                        'Visa',

                    'service_reference' =>
                        'VISA-001',

                    'sub_total' =>
                        5000,

                    'discount' =>
                        0,

                    'tax' =>
                        0,

                    'total_amount' =>
                        5000,

                    'paid_amount' =>
                        0,

                    'balance_amount' =>
                        5000,

                    'status' =>
                        'Unpaid',
                ],
            );

        $policy->forceFill(
            [
                'invoice_id' =>
                    $unrelated->id,
            ],
        )->saveQuietly();

        $this->expectException(
            ValidationException::class,
        );

        app(
            TravelInsuranceInvoiceService::class,
        )->createOrSync(
            $policy,
        );
    }

    private function createApprovedFixturePayment(
        TravelInsurancePolicy $policy,
        float $amount,
    ): TravelInsurancePayment {
        return TravelInsurancePayment::query()->create(
            [
                'travel_insurance_policy_id' =>
                    $policy->id,

                'payment_date' =>
                    today(),

                'amount' =>
                    $amount,

                'payment_method' =>
                    'Cash',

                'status' =>
                    'Approved',

                'posting_status' =>
                    'Pending',
            ],
        );
    }

    private function ensureAccountingAccounts(): void
    {
        AccountHead::query()->firstOrCreate(
            [
                'name' =>
                    'Cash in Hand',
            ],
            [
                'code' =>
                    'A-10001',

                'type' =>
                    'Asset',

                'sub_type' =>
                    'Cash',

                'opening_debit' =>
                    0,

                'opening_credit' =>
                    0,

                'status' =>
                    'Active',
            ],
        );

        AccountHead::query()->firstOrCreate(
            [
                'name' =>
                    'Bank Account',
            ],
            [
                'code' =>
                    'A-10002',

                'type' =>
                    'Asset',

                'sub_type' =>
                    'Bank',

                'opening_debit' =>
                    0,

                'opening_credit' =>
                    0,

                'status' =>
                    'Active',
            ],
        );
    }

    private function createPolicy(): TravelInsurancePolicy
    {
        $suffix =
            fake()->unique()->numerify(
                '######',
            );

        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        "Invoice Insurance Customer {$suffix}",

                    'mobile' =>
                        "03{$suffix}000",

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
                        "Invoice Insurance Provider {$suffix}",

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
                    "II{$suffix}",

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
                    today()->addDays(
                        10,
                    ),

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
        );
    }

    private function createUser(): User
    {
        return User::query()->create(
            [
                'name' =>
                    'Insurance Invoice Test User',

                'email' =>
                    fake()->unique()->safeEmail(),

                'password' =>
                    Hash::make(
                        'password',
                    ),

                'role' =>
                    'Admin',

                'is_active' =>
                    true,

                'permissions' =>
                    [],
            ],
        );
    }
}