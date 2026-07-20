<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\User;
use App\Services\CustomerPassportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassportValidityUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_active_passport_document_supplies_defaults(): void
    {
        $customer = Customer::query()->create([
            'name' =>
                'Passport Test Customer',
            'mobile' =>
                '03000000001',
            'passport_no' =>
                'PAK123456',
        ]);

        $this->createCustomerDocument([
            'customer_id' =>
                $customer->id,
            'service_type' =>
                'Visa',
            'document_type' =>
                'CNIC',
            'document_types' =>
                ['CNIC'],
            'expiry_date' =>
                '2035-01-01',
            'status' =>
                'Active',
        ]);

        $this->createCustomerDocument([
            'customer_id' =>
                $customer->id,
            'service_type' =>
                'Visa',
            'document_type' =>
                'Passport',
            'document_types' =>
                ['Passport'],
            'expiry_date' =>
                '2028-04-10',
            'status' =>
                'Active',
        ]);

        $latestPassport =
            $this->createCustomerDocument([
                'customer_id' =>
                    $customer->id,
                'service_type' =>
                    'Visa',
                'document_type' =>
                    'Passport, Visa',
                'document_types' => [
                    'Passport',
                    'Visa',
                ],
                'expiry_date' =>
                    '2029-06-20',
                'status' =>
                    'Active',
            ]);

        $defaults = app(
            CustomerPassportService::class,
        )->defaultsForCustomer(
            $customer->id,
        );

        $this->assertSame(
            'PAK123456',
            $defaults['passport_no'],
        );

        $this->assertSame(
            '2029-06-20',
            $defaults[
                'passport_expiry_date'
            ],
        );

        $this->assertSame(
            $latestPassport->id,
            $defaults['document_id'],
        );
    }

    public function test_cancelled_passport_document_is_ignored(): void
    {
        $customer = Customer::query()->create([
            'name' =>
                'Cancelled Passport Customer',
            'mobile' =>
                '03000000002',
            'passport_no' =>
                'ACTIVE-123',
        ]);

        $activePassport =
            $this->createCustomerDocument([
                'customer_id' =>
                    $customer->id,
                'document_type' =>
                    'Passport',
                'document_types' =>
                    ['Passport'],
                'expiry_date' =>
                    '2028-01-01',
                'status' =>
                    'Active',
            ]);

        $this->createCustomerDocument([
            'customer_id' =>
                $customer->id,
            'document_type' =>
                'Passport',
            'document_types' =>
                ['Passport'],
            'expiry_date' =>
                '2035-01-01',
            'status' =>
                'Cancelled',
        ]);

        $defaults = app(
            CustomerPassportService::class,
        )->defaultsForCustomer(
            $customer->id,
        );

        $this->assertSame(
            $activePassport->id,
            $defaults['document_id'],
        );

        $this->assertSame(
            '2028-01-01',
            $defaults[
                'passport_expiry_date'
            ],
        );
    }

    public function test_passport_type_array_is_detected(): void
    {
        $document =
            new CustomerDocument([
                'document_type' => null,
                'document_types' => [
                    'CNIC',
                    'Passport',
                ],
            ]);

        $this->assertTrue(
            app(
                CustomerPassportService::class,
            )->isPassportDocument(
                $document,
            ),
        );
    }

    public function test_customer_without_document_still_supplies_passport_number(): void
    {
        $customer = Customer::query()->create([
            'name' =>
                'No Document Customer',
            'mobile' =>
                '03000000003',
            'passport_no' =>
                'NO-DOC-123',
        ]);

        $defaults = app(
            CustomerPassportService::class,
        )->defaultsForCustomer(
            $customer->id,
        );

        $this->assertSame(
            'NO-DOC-123',
            $defaults['passport_no'],
        );

        $this->assertNull(
            $defaults[
                'passport_expiry_date'
            ],
        );
    }

    public function test_resource_files_include_passport_validity_ui(): void
    {
        $form = file_get_contents(
            app_path(
                'Filament/Resources/'
                .'VisaApplications/Schemas/'
                .'VisaApplicationForm.php',
            ),
        );

        $infolist = file_get_contents(
            app_path(
                'Filament/Resources/'
                .'VisaApplications/Schemas/'
                .'VisaApplicationInfolist.php',
            ),
        );

        $table = file_get_contents(
            app_path(
                'Filament/Resources/'
                .'VisaApplications/Tables/'
                .'VisaApplicationsTable.php',
            ),
        );

        $this->assertStringContainsString(
            'CustomerPassportService',
            $form,
        );

        $this->assertStringContainsString(
            "DatePicker::make('passport_expiry_date')",
            $form,
        );

        $this->assertStringContainsString(
            'passport_validity_preview',
            $form,
        );

        $this->assertStringContainsString(
            'passport_validity_status',
            $infolist,
        );

        $this->assertStringContainsString(
            "SelectFilter::make(",
            $table,
        );

        $this->assertStringContainsString(
            'passport_validity_status',
            $table,
        );
    }

    public function test_authorized_staff_can_render_visa_pages(): void
    {
        $user = User::factory()->create([
            'role' =>
                'Sales Staff',
            'is_active' =>
                true,
            'permissions' => [
                'visa_applications_view',
                'visa_applications_create',
            ],
        ]);

        $createResponse = $this
            ->actingAs($user)
            ->get(
                route(
                    'filament.admin.resources.'
                    .'visa-applications.create',
                ),
            );

        $createResponse->assertOk();

        $createResponse->assertSeeText(
            'Passport Expiry Date',
        );

        $createResponse->assertSeeText(
            'Passport Validity Check',
        );

        $listResponse = $this
            ->actingAs($user)
            ->get(
                route(
                    'filament.admin.resources.'
                    .'visa-applications.index',
                ),
            );

        $listResponse->assertOk();

        $listResponse->assertSeeText(
            'Passport Validity',
        );
    }

    private function createCustomerDocument(
        array $attributes,
    ): CustomerDocument {
        $token = str_replace(
            '.',
            '',
            uniqid('passport-test-', true),
        );

        $fileName = $token.'.pdf';

        $filePath =
            'customer-documents/'.$fileName;

        return CustomerDocument::query()
            ->create(
                array_merge(
                    [
                        'service_type' =>
                            'Visa',

                        'document_type' =>
                            'Other',

                        'document_types' =>
                            ['Other'],

                        'title' =>
                            'Passport Test Document',

                        'file_name' =>
                            $fileName,

                        'file_path' =>
                            $filePath,

                        'file_type' =>
                            'PDF',

                        'file_size' =>
                            1024,

                        'documents' => [
                            [
                                'file_name' =>
                                    $fileName,

                                'file_path' =>
                                    $filePath,

                                'file_type' =>
                                    'PDF',

                                'file_size' =>
                                    1024,
                            ],
                        ],

                        'status' =>
                            'Active',
                    ],
                    $attributes,
                ),
            );
    }
}
