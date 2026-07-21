<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPaymentUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPortalProfilePaymentsTest extends TestCase
{
    use RefreshDatabase;

    private static int $agentSequence = 0;

    public function test_profile_routes_and_forms_are_available(): void
    {
        $customer =
            $this->agent();

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.profile',
                ),
            )
            ->assertOk()
            ->assertSee(
                route(
                    'customer.portal.profile.update',
                ),
                false,
            )
            ->assertSee(
                route(
                    'customer.portal.profile.password',
                ),
                false,
            )
            ->assertSee(
                'Save Profile',
            )
            ->assertSee(
                'Change Password',
            );

        $this->assertSame(
            [
                'PATCH',
            ],
            Route::getRoutes()
                ->getByName(
                    'customer.portal.profile.update',
                )
                ->methods(),
        );

        $this->assertSame(
            [
                'PUT',
            ],
            Route::getRoutes()
                ->getByName(
                    'customer.portal.profile.password',
                )
                ->methods(),
        );
    }

    public function test_customer_can_update_safe_profile_fields_only(): void
    {
        $customer =
            $this->agent([
                'cnic' =>
                    '3520212345671',

                'passport_no' =>
                    'AB1234567',

                'notes' =>
                    'Internal office notes',
            ]);

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->patch(
                route(
                    'customer.portal.profile.update',
                ),
                [
                    'name' =>
                        'Updated Portal Agent',

                    'father_name' =>
                        'Updated Father',

                    'mobile' =>
                        '03011112222',

                    'whatsapp' =>
                        '03033334444',

                    'email' =>
                        'UPDATED@EXAMPLE.TEST',

                    'date_of_birth' =>
                        '1990-01-01',

                    'address' =>
                        'Updated Address',

                    'city' =>
                        'Multan',

                    'country' =>
                        'Pakistan',

                    'customer_type' =>
                        'Customer',

                    'status' =>
                        'Inactive',

                    'cnic' =>
                        '0000000000000',

                    'passport_no' =>
                        'INVALID',

                    'notes' =>
                        'Customer changed notes',
                ],
            )
            ->assertRedirect(
                route(
                    'customer.portal.profile',
                ),
            )
            ->assertSessionHas(
                'success',
            )
            ->assertSessionHas(
                'customer_portal_name',
                'Updated Portal Agent',
            );

        $customer->refresh();

        $this->assertSame(
            'Updated Portal Agent',
            $customer->name,
        );

        $this->assertSame(
            'updated@example.test',
            $customer->email,
        );

        $this->assertSame(
            'Agent',
            $customer->customer_type,
        );

        $this->assertSame(
            'Active',
            $customer->status,
        );

        $this->assertSame(
            '3520212345671',
            $customer->cnic,
        );

        $this->assertSame(
            'AB1234567',
            $customer->passport_no,
        );

        $this->assertSame(
            'Internal office notes',
            $customer->notes,
        );
    }

    public function test_profile_identifiers_cannot_conflict_with_another_customer(): void
    {
        $customer =
            $this->agent();

        $other =
            $this->agent([
                'passport_no' =>
                    'CONFLICT123',
            ]);

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->from(
                route(
                    'customer.portal.profile',
                ),
            )
            ->patch(
                route(
                    'customer.portal.profile.update',
                ),
                [
                    'name' =>
                        $customer->name,

                    'mobile' =>
                        $other->passport_no,

                    'whatsapp' =>
                        $customer->whatsapp,

                    'email' =>
                        $customer->email,
                ],
            )
            ->assertRedirect(
                route(
                    'customer.portal.profile',
                ),
            )
            ->assertSessionHasErrors(
                'mobile',
            );

        $this->assertNotSame(
            $other->passport_no,
            $customer->refresh()
                ->mobile,
        );
    }

    public function test_customer_can_change_password_and_current_session_is_refreshed(): void
    {
        $customer =
            $this->agent();

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->put(
                route(
                    'customer.portal.profile.password',
                ),
                [
                    'current_password' =>
                        'PortalPassword123!',

                    'password' =>
                        'NewPortalPassword456',

                    'password_confirmation' =>
                        'NewPortalPassword456',
                ],
            )
            ->assertRedirect(
                route(
                    'customer.portal.profile',
                ),
            )
            ->assertSessionHas(
                'success',
            );

        $customer->refresh();

        $this->assertTrue(
            Hash::check(
                'NewPortalPassword456',
                $customer->portal_password,
            ),
        );

        $this->assertSame(
            hash(
                'sha256',
                $customer->portal_password,
            ),
            session(
                'customer_portal_password_fingerprint',
            ),
        );
    }

    public function test_wrong_current_password_does_not_change_portal_password(): void
    {
        $customer =
            $this->agent();

        $originalHash =
            $customer->portal_password;

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->from(
                route(
                    'customer.portal.profile',
                ),
            )
            ->put(
                route(
                    'customer.portal.profile.password',
                ),
                [
                    'current_password' =>
                        'WrongPassword123',

                    'password' =>
                        'NewPortalPassword456',

                    'password_confirmation' =>
                        'NewPortalPassword456',
                ],
            )
            ->assertRedirect(
                route(
                    'customer.portal.profile',
                ),
            )
            ->assertSessionHasErrors(
                'current_password',
            );

        $this->assertSame(
            $originalHash,
            $customer->refresh()
                ->portal_password,
        );
    }

    public function test_password_change_invalidates_other_portal_sessions(): void
    {
        $customer =
            $this->agent();

        $oldFingerprint =
            hash(
                'sha256',
                $customer->portal_password,
            );

        $customer->forceFill([
            'portal_password' =>
                Hash::make(
                    'ChangedByAnotherSession789',
                ),
        ])->save();

        $session =
            $this->portalSession(
                $customer,
            );

        $session['customer_portal_password_fingerprint'] =
            $oldFingerprint;

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.dashboard',
                ),
            )
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            )
            ->assertSessionHas(
                'error',
            );
    }

    public function test_portal_password_is_hidden_from_customer_serialization(): void
    {
        $customer =
            $this->agent();

        $array =
            $customer->toArray();

        $this->assertArrayNotHasKey(
            'portal_password',
            $array,
        );

        $this->assertStringNotContainsString(
            'PortalPassword123!',
            $customer->toJson(),
        );
    }

    public function test_payment_receipt_is_required_and_stored_privately(): void
    {
        Storage::fake(
            'local',
        );

        Storage::fake(
            'public',
        );

        $customer =
            $this->agent();

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->post(
                route(
                    'customer.portal.payment_uploads.store',
                ),
                [
                    'service_type' =>
                        'Visa',

                    'reference_no' =>
                        'INV-PORTAL-PRIVATE',

                    'amount' =>
                        25000,

                    'payment_method' =>
                        'Bank Transfer',

                    'payment_date' =>
                        today()->format(
                            'Y-m-d',
                        ),

                    'transaction_id' =>
                        'TXN-PRIVATE-001',

                    'receipt_file' =>
                        UploadedFile::fake()
                            ->create(
                                'receipt.pdf',
                                100,
                                'application/pdf',
                            ),

                    'customer_notes' =>
                        'Private portal payment',
                ],
            )
            ->assertRedirect(
                route(
                    'customer.portal.payment_uploads',
                ),
            )
            ->assertSessionHas(
                'success',
            );

        $upload =
            CustomerPaymentUpload::query()
                ->firstOrFail();

        $this->assertSame(
            $customer->id,
            $upload->customer_id,
        );

        $this->assertSame(
            'Pending',
            $upload->status,
        );

        $this->assertStringStartsWith(
            'customer/payment-receipts/',
            $upload->receipt_file,
        );

        Storage::disk(
            'local',
        )->assertExists(
            $upload->receipt_file,
        );

        Storage::disk(
            'public',
        )->assertMissing(
            $upload->receipt_file,
        );
    }

    public function test_payment_upload_page_hides_internal_fields_and_raw_storage_path(): void
    {
        Storage::fake(
            'local',
        );

        $customer =
            $this->agent();

        $upload =
            $this->paymentUpload(
                $customer,
                [
                    'admin_notes' =>
                        'SECRET INTERNAL ADMIN NOTE',

                    'receipt_file' =>
                        'customer/payment-receipts/private.pdf',
                ],
            );

        Storage::disk(
            'local',
        )->put(
            $upload->receipt_file,
            'private receipt',
        );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.payment_uploads',
                ),
            )
            ->assertOk()
            ->assertSee(
                $upload->upload_no,
            )
            ->assertSee(
                route(
                    'customer.portal.payment_uploads.receipt',
                    [
                        'customerPaymentUpload' =>
                            $upload,
                    ],
                ),
                false,
            )
            ->assertDontSee(
                'SECRET INTERNAL ADMIN NOTE',
            )
            ->assertDontSee(
                $upload->receipt_file,
            );
    }

    public function test_customer_can_download_only_own_private_receipt(): void
    {
        Storage::fake(
            'local',
        );

        $first =
            $this->agent();

        $second =
            $this->agent();

        $firstUpload =
            $this->paymentUpload(
                $first,
                [
                    'receipt_file' =>
                        'customer/payment-receipts/first.pdf',
                ],
            );

        $secondUpload =
            $this->paymentUpload(
                $second,
                [
                    'receipt_file' =>
                        'customer/payment-receipts/second.pdf',
                ],
            );

        Storage::disk(
            'local',
        )->put(
            $firstUpload->receipt_file,
            'first private receipt',
        );

        Storage::disk(
            'local',
        )->put(
            $secondUpload->receipt_file,
            'second private receipt',
        );

        $this->withSession(
            $this->portalSession(
                $first,
            ),
        )
            ->get(
                route(
                    'customer.portal.payment_uploads.receipt',
                    [
                        'customerPaymentUpload' =>
                            $firstUpload,
                    ],
                ),
            )
            ->assertOk()
            ->assertDownload();

        $this->withSession(
            $this->portalSession(
                $first,
            ),
        )
            ->get(
                route(
                    'customer.portal.payment_uploads.receipt',
                    [
                        'customerPaymentUpload' =>
                            $secondUpload,
                    ],
                ),
            )
            ->assertForbidden();

        $this->flushSession();

        $this->get(
            route(
                'customer.portal.payment_uploads.receipt',
                [
                    'customerPaymentUpload' =>
                        $firstUpload,
                ],
            ),
        )
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            );
    }

    public function test_legacy_public_receipt_is_supported_and_deleted_with_record(): void
    {
        Storage::fake(
            'local',
        );

        Storage::fake(
            'public',
        );

        $customer =
            $this->agent();

        $upload =
            $this->paymentUpload(
                $customer,
                [
                    'receipt_file' =>
                        'customer-payment-uploads/legacy.pdf',
                ],
            );

        Storage::disk(
            'public',
        )->put(
            $upload->receipt_file,
            'legacy public receipt',
        );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.payment_uploads.receipt',
                    [
                        'customerPaymentUpload' =>
                            $upload,
                    ],
                ),
            )
            ->assertOk()
            ->assertDownload();

        $upload->delete();

        Storage::disk(
            'public',
        )->assertMissing(
            'customer-payment-uploads/legacy.pdf',
        );
    }

    public function test_payment_upload_source_uses_private_storage(): void
    {
        $controller =
            file_get_contents(
                app_path(
                    'Http/Controllers/CustomerPortalController.php',
                ),
            );

        $filamentForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/CustomerPaymentUploads/Schemas/CustomerPaymentUploadForm.php',
                ),
            );

        $customerModel =
            file_get_contents(
                app_path(
                    'Models/Customer.php',
                ),
            );

        $this->assertStringContainsString(
            "'customer/payment-receipts'",
            $controller,
        );

        $this->assertStringContainsString(
            "->disk('local')",
            $filamentForm,
        );

        $this->assertStringContainsString(
            "->visibility('private')",
            $filamentForm,
        );

        $this->assertStringNotContainsString(
            "->disk('public')",
            $filamentForm,
        );

        $this->assertStringContainsString(
            "'portal_password'",
            $customerModel,
        );
    }

    private function agent(
        array $attributes = [],
    ): Customer {
        self::$agentSequence++;

        $sequence =
            str_pad(
                (string) self::$agentSequence,
                4,
                '0',
                STR_PAD_LEFT,
            );

        return Customer::query()
            ->create(
                array_merge(
                    [
                        'name' =>
                            'Portal Agent '.$sequence,

                        'mobile' =>
                            '0300000'.$sequence,

                        'whatsapp' =>
                            '0310000'.$sequence,

                        'email' =>
                            'portal-profile-'
                            .$sequence
                            .'@example.test',

                        'customer_type' =>
                            'Agent',

                        'status' =>
                            'Active',

                        'portal_password' =>
                            Hash::make(
                                'PortalPassword123!',
                            ),

                        'portal_registered_at' =>
                            now(),
                    ],
                    $attributes,
                ),
            );
    }

    private function paymentUpload(
        Customer $customer,
        array $attributes = [],
    ): CustomerPaymentUpload {
        return CustomerPaymentUpload::query()
            ->create(
                array_merge(
                    [
                        'customer_id' =>
                            $customer->id,

                        'service_type' =>
                            'Visa',

                        'reference_no' =>
                            'INV-PRIVATE-001',

                        'amount' =>
                            1000,

                        'payment_method' =>
                            'Bank Transfer',

                        'payment_date' =>
                            today(),

                        'transaction_id' =>
                            'TXN-PRIVATE',

                        'receipt_file' =>
                            'customer/payment-receipts/receipt.pdf',

                        'status' =>
                            'Pending',

                        'customer_notes' =>
                            'Customer visible note',
                    ],
                    $attributes,
                ),
            );
    }

    private function portalSession(
        Customer $customer,
    ): array {
        return [
            'customer_portal_id' =>
                $customer->id,

            'customer_portal_name' =>
                $customer->name,

            'customer_portal_last_activity' =>
                time(),

            'customer_portal_password_fingerprint' =>
                hash(
                    'sha256',
                    $customer->portal_password,
                ),
        ];
    }
}