<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use App\Services\TravelInsurancePrivateFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TravelInsurancePrivateFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_file_routes_are_registered(): void
    {
        $policy =
            $this->createPolicy();

        $payment =
            $this->createPayment(
                $policy,
            );

        $this->assertSame(
            "/travel-insurance-policies/{$policy->id}/document",
            route(
                'travel-insurance-policies.document.download',
                [
                    'travelInsurancePolicy' =>
                        $policy,
                ],
                absolute: false,
            ),
        );

        $this->assertSame(
            "/travel-insurance-payments/{$payment->id}/receipt",
            route(
                'travel-insurance-payments.receipt.download',
                [
                    'travelInsurancePayment' =>
                        $payment,
                ],
                absolute: false,
            ),
        );
    }

    public function test_upload_forms_use_private_local_storage_only(): void
    {
        $policyForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/TravelInsurancePolicies/Schemas/TravelInsurancePolicyForm.php',
                ),
            );

        $paymentForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/TravelInsurancePayments/Schemas/TravelInsurancePaymentForm.php',
                ),
            );

        foreach (
            [
                $policyForm,
                $paymentForm,
            ]
            as $source
        ) {
            $this->assertMatchesRegularExpression(
                "/->disk\(\s*'local',\s*\)/",
                $source,
            );

            $this->assertMatchesRegularExpression(
                "/->visibility\(\s*'private',\s*\)/",
                $source,
            );

            $this->assertStringNotContainsString(
                "'public'",
                $source,
            );
        }

        $this->assertStringContainsString(
            "'travel-insurance/policies'",
            $policyForm,
        );

        $this->assertStringContainsString(
            "'travel-insurance/receipts'",
            $paymentForm,
        );
    }

    public function test_private_file_permissions_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        $adminDefaults =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        foreach (
            [
                'travel_insurance_documents_download',
                'travel_insurance_receipts_download',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );

            $this->assertContains(
                $permission,
                $adminDefaults,
            );
        }
    }

    public function test_authorized_user_can_download_policy_document(): void
    {
        Storage::fake(
            'local',
        );

        $policy =
            $this->createPolicy();

        $path =
            'travel-insurance/policies/policy-test.pdf';

        Storage::disk(
            'local',
        )->put(
            $path,
            'private-policy-content',
        );

        $policy->forceFill(
            [
                'policy_document_path' =>
                    $path,

                'policy_document_name' =>
                    'customer-policy.pdf',

                'policy_document_mime' =>
                    'application/pdf',

                'policy_document_size' =>
                    strlen(
                        'private-policy-content',
                    ),
            ],
        )->saveQuietly();

        $user =
            $this->createUser(
                [
                    'travel_insurance_documents_download',
                ],
            );

        $this->actingAs(
            $user,
        );

        $this->get(
            route(
                'travel-insurance-policies.document.download',
                [
                    'travelInsurancePolicy' =>
                        $policy,
                ],
            ),
        )
            ->assertOk()
            ->assertDownload(
                'customer-policy.pdf',
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff',
            );
    }

    public function test_user_without_permission_cannot_download_policy_document(): void
    {
        Storage::fake(
            'local',
        );

        $policy =
            $this->createPolicy();

        $path =
            'travel-insurance/policies/blocked.pdf';

        Storage::disk(
            'local',
        )->put(
            $path,
            'blocked',
        );

        $policy->forceFill(
            [
                'policy_document_path' =>
                    $path,

                'policy_document_name' =>
                    'blocked.pdf',
            ],
        )->saveQuietly();

        $this->actingAs(
            $this->createUser(
                [],
            ),
        );

        $this->get(
            route(
                'travel-insurance-policies.document.download',
                [
                    'travelInsurancePolicy' =>
                        $policy,
                ],
            ),
        )->assertForbidden();
    }

    public function test_authorized_user_can_download_payment_receipt(): void
    {
        Storage::fake(
            'local',
        );

        $payment =
            $this->createPayment(
                $this->createPolicy(),
            );

        $path =
            'travel-insurance/receipts/receipt-test.pdf';

        Storage::disk(
            'local',
        )->put(
            $path,
            'private-receipt-content',
        );

        $payment->forceFill(
            [
                'receipt_path' =>
                    $path,

                'receipt_name' =>
                    'insurance-receipt.pdf',

                'receipt_mime' =>
                    'application/pdf',

                'receipt_size' =>
                    strlen(
                        'private-receipt-content',
                    ),
            ],
        )->saveQuietly();

        $this->actingAs(
            $this->createUser(
                [
                    'travel_insurance_receipts_download',
                ],
            ),
        );

        $this->get(
            route(
                'travel-insurance-payments.receipt.download',
                [
                    'travelInsurancePayment' =>
                        $payment,
                ],
            ),
        )
            ->assertOk()
            ->assertDownload(
                'insurance-receipt.pdf',
            );
    }

    public function test_unowned_or_missing_private_paths_return_not_found(): void
    {
        Storage::fake(
            'local',
        );

        $policy =
            $this->createPolicy();

        $policy->forceFill(
            [
                'policy_document_path' =>
                    'customer-documents/not-insurance.pdf',

                'policy_document_name' =>
                    'not-insurance.pdf',
            ],
        )->saveQuietly();

        $user =
            $this->createUser(
                [
                    'travel_insurance_documents_download',
                ],
            );

        $this->actingAs(
            $user,
        );

        $this->get(
            route(
                'travel-insurance-policies.document.download',
                [
                    'travelInsurancePolicy' =>
                        $policy,
                ],
            ),
        )->assertNotFound();

        $policy->forceFill(
            [
                'policy_document_path' =>
                    'travel-insurance/policies/missing.pdf',
            ],
        )->saveQuietly();

        $this->get(
            route(
                'travel-insurance-policies.document.download',
                [
                    'travelInsurancePolicy' =>
                        $policy,
                ],
            ),
        )->assertNotFound();
    }

    public function test_replacing_private_files_updates_metadata_and_deletes_old_files(): void
    {
        Storage::fake(
            'local',
        );

        $policy =
            $this->createPolicy();

        $oldPolicyPath =
            'travel-insurance/policies/old-policy.pdf';

        $newPolicyPath =
            'travel-insurance/policies/new-policy.pdf';

        Storage::disk(
            'local',
        )->put(
            $oldPolicyPath,
            'old-policy',
        );

        Storage::disk(
            'local',
        )->put(
            $newPolicyPath,
            'new-policy-content',
        );

        $policy->forceFill(
            [
                'policy_document_path' =>
                    $newPolicyPath,

                'policy_document_name' =>
                    '../safe-policy.pdf',
            ],
        )->saveQuietly();

        app(
            TravelInsurancePrivateFileService::class,
        )->syncPolicyDocument(
            $policy,
            $oldPolicyPath,
        );

        $policy->refresh();

        Storage::disk(
            'local',
        )->assertMissing(
            $oldPolicyPath,
        );

        Storage::disk(
            'local',
        )->assertExists(
            $newPolicyPath,
        );

        $this->assertSame(
            'safe-policy.pdf',
            $policy->policy_document_name,
        );

        $this->assertGreaterThan(
            0,
            (int) $policy->policy_document_size,
        );

        $payment =
            $this->createPayment(
                $policy,
            );

        $oldReceiptPath =
            'travel-insurance/receipts/old-receipt.pdf';

        $newReceiptPath =
            'travel-insurance/receipts/new-receipt.pdf';

        Storage::disk(
            'local',
        )->put(
            $oldReceiptPath,
            'old-receipt',
        );

        Storage::disk(
            'local',
        )->put(
            $newReceiptPath,
            'new-receipt-content',
        );

        $payment->forceFill(
            [
                'receipt_path' =>
                    $newReceiptPath,

                'receipt_name' =>
                    '../safe-receipt.pdf',
            ],
        )->saveQuietly();

        app(
            TravelInsurancePrivateFileService::class,
        )->syncPaymentReceipt(
            $payment,
            $oldReceiptPath,
        );

        $payment->refresh();

        Storage::disk(
            'local',
        )->assertMissing(
            $oldReceiptPath,
        );

        Storage::disk(
            'local',
        )->assertExists(
            $newReceiptPath,
        );

        $this->assertSame(
            'safe-receipt.pdf',
            $payment->receipt_name,
        );

        $this->assertGreaterThan(
            0,
            (int) $payment->receipt_size,
        );
    }

    public function test_deleting_payment_removes_private_receipt(): void
    {
        Storage::fake(
            'local',
        );

        $payment =
            $this->createPayment(
                $this->createPolicy(),
            );

        $path =
            'travel-insurance/receipts/delete-payment.pdf';

        Storage::disk(
            'local',
        )->put(
            $path,
            'delete-payment',
        );

        $payment->forceFill(
            [
                'receipt_path' =>
                    $path,
            ],
        )->saveQuietly();

        $payment->delete();

        Storage::disk(
            'local',
        )->assertMissing(
            $path,
        );
    }

    public function test_deleting_policy_removes_policy_document_and_cascaded_receipts(): void
    {
        Storage::fake(
            'local',
        );

        $policy =
            $this->createPolicy();

        $payment =
            $this->createPayment(
                $policy,
            );

        $policyPath =
            'travel-insurance/policies/delete-policy.pdf';

        $receiptPath =
            'travel-insurance/receipts/delete-cascade.pdf';

        Storage::disk(
            'local',
        )->put(
            $policyPath,
            'policy',
        );

        Storage::disk(
            'local',
        )->put(
            $receiptPath,
            'receipt',
        );

        $policy->forceFill(
            [
                'policy_document_path' =>
                    $policyPath,
            ],
        )->saveQuietly();

        $payment->forceFill(
            [
                'receipt_path' =>
                    $receiptPath,
            ],
        )->saveQuietly();

        $policy->delete();

        Storage::disk(
            'local',
        )->assertMissing(
            $policyPath,
        );

        Storage::disk(
            'local',
        )->assertMissing(
            $receiptPath,
        );

        $this->assertDatabaseMissing(
            'travel_insurance_policies',
            [
                'id' =>
                    $policy->id,
            ],
        );

        $this->assertDatabaseMissing(
            'travel_insurance_payments',
            [
                'id' =>
                    $payment->id,
            ],
        );
    }

    private function createPayment(
        TravelInsurancePolicy $policy,
    ): TravelInsurancePayment {
        return TravelInsurancePayment::query()->create(
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

                'posting_status' =>
                    'Pending',
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
                        "Private File Customer {$suffix}",

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
                        "Private File Provider {$suffix}",

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
                    "PF{$suffix}",

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
                    'Draft',

                'approval_status' =>
                    'Pending',
            ],
        );
    }

    private function createUser(
        array $permissions,
    ): User {
        return User::query()->create(
            [
                'name' =>
                    'Private Insurance File User',

                'email' =>
                    fake()->unique()->safeEmail(),

                'password' =>
                    Hash::make(
                        'password',
                    ),

                'role' =>
                    'Sales Staff',

                'is_active' =>
                    true,

                'permissions' =>
                    $permissions,
            ],
        );
    }
}