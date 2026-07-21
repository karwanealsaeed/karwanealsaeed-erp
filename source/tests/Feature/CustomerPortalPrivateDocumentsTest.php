<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPortalPrivateDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private static int $agentSequence = 0;

    public function test_customer_document_upload_sources_use_private_storage(): void
    {
        $form =
            file_get_contents(
                app_path(
                    'Filament/Resources/CustomerDocuments/Forms/CustomerDocumentForm.php',
                ),
            );

        $createPage =
            file_get_contents(
                app_path(
                    'Filament/Resources/CustomerDocuments/Pages/CreateCustomerDocument.php',
                ),
            );

        $editPage =
            file_get_contents(
                app_path(
                    'Filament/Resources/CustomerDocuments/Pages/EditCustomerDocument.php',
                ),
            );

        $this->assertStringContainsString(
            "->disk('local')",
            $form,
        );

        $this->assertStringContainsString(
            "->visibility('private')",
            $form,
        );

        $this->assertStringNotContainsString(
            "->disk('public')",
            $form,
        );

        $this->assertStringContainsString(
            "Storage::disk('local')",
            $createPage,
        );

        $this->assertStringContainsString(
            "Storage::disk('local')",
            $editPage,
        );

        $this->assertStringNotContainsString(
            "Storage::disk('public')",
            $createPage,
        );

        $this->assertStringNotContainsString(
            "Storage::disk('public')",
            $editPage,
        );
    }

    public function test_portal_customer_can_download_private_document(): void
    {
        Storage::fake(
            'local',
        );

        Storage::fake(
            'public',
        );

        $customer =
            $this->agent();

        $document =
            $this->document(
                $customer,
                'customer-documents/private-passport.pdf',
            );

        Storage::disk(
            'local',
        )->put(
            'customer-documents/private-passport.pdf',
            'private customer passport',
        );

        Storage::disk(
            'public',
        )->assertMissing(
            'customer-documents/private-passport.pdf',
        );

        $response =
            $this->withSession(
                $this->portalSession(
                    $customer,
                ),
            )
                ->get(
                    route(
                        'customer.portal.documents.download',
                        [
                            'customerDocument' =>
                                $document,

                            'fileIndex' =>
                                0,
                        ],
                    ),
                );

        $response
            ->assertOk()
            ->assertDownload(
                'private-passport.pdf',
            );

        $cacheControl =
            (string) $response->headers
                ->get(
                    'cache-control',
                );

        $this->assertStringContainsString(
            'private',
            $cacheControl,
        );

        $this->assertStringContainsString(
            'no-store',
            $cacheControl,
        );

        $this->assertStringContainsString(
            'max-age=0',
            $cacheControl,
        );
    }

    public function test_document_deletion_removes_private_and_legacy_public_files(): void
    {
        Storage::fake(
            'local',
        );

        Storage::fake(
            'public',
        );

        $customer =
            $this->agent();

        $document =
            CustomerDocument::query()
                ->create([
                    'customer_id' =>
                        $customer->id,

                    'service_type' =>
                        'Visa',

                    'document_type' =>
                        'Passport',

                    'document_types' => [
                        'Passport',
                    ],

                    'title' =>
                        'Mixed Storage Documents',

                    'file_name' =>
                        'private.pdf',

                    'file_path' =>
                        'customer-documents/private.pdf',

                    'file_type' =>
                        'PDF',

                    'file_size' =>
                        100,

                    'documents' => [
                        [
                            'file_name' =>
                                'private.pdf',

                            'file_path' =>
                                'customer-documents/private.pdf',

                            'file_type' =>
                                'PDF',

                            'file_size' =>
                                100,
                        ],

                        [
                            'file_name' =>
                                'legacy.pdf',

                            'file_path' =>
                                'customer-documents/legacy.pdf',

                            'file_type' =>
                                'PDF',

                            'file_size' =>
                                100,
                        ],
                    ],

                    'status' =>
                        'Active',
                ]);

        Storage::disk(
            'local',
        )->put(
            'customer-documents/private.pdf',
            'private document',
        );

        Storage::disk(
            'public',
        )->put(
            'customer-documents/legacy.pdf',
            'legacy public document',
        );

        $document->delete();

        Storage::disk(
            'local',
        )->assertMissing(
            'customer-documents/private.pdf',
        );

        Storage::disk(
            'public',
        )->assertMissing(
            'customer-documents/legacy.pdf',
        );
    }

    public function test_private_document_migration_covers_orphan_public_files(): void
    {
        $migration =
            file_get_contents(
                database_path(
                    'migrations/2026_07_21_130000_move_customer_documents_to_private_storage.php',
                ),
            );

        $controller =
            file_get_contents(
                app_path(
                    'Http/Controllers/CustomerDocumentDownloadController.php',
                ),
            );

        $this->assertStringContainsString(
            "->allFiles(",
            $migration,
        );

        $this->assertStringContainsString(
            "'customer-documents'",
            $migration,
        );

        $this->assertStringContainsString(
            "Storage::disk(",
            $controller,
        );

        $this->assertStringContainsString(
            "'local'",
            $controller,
        );

        $this->assertStringContainsString(
            "'public'",
            $controller,
        );

        $this->assertStringContainsString(
            'private, no-store, max-age=0',
            $controller,
        );
    }

    private function agent(): Customer
    {
        self::$agentSequence++;

        $sequence =
            str_pad(
                (string) self::$agentSequence,
                4,
                '0',
                STR_PAD_LEFT,
            );

        return Customer::query()
            ->create([
                'name' =>
                    'Private Document Agent '
                    .$sequence,

                'mobile' =>
                    '0340000'
                    .$sequence,

                'email' =>
                    'private-document-'
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
            ]);
    }

    private function document(
        Customer $customer,
        string $filePath,
    ): CustomerDocument {
        return CustomerDocument::query()
            ->create([
                'customer_id' =>
                    $customer->id,

                'service_type' =>
                    'Visa',

                'document_type' =>
                    'Passport',

                'document_types' => [
                    'Passport',
                ],

                'title' =>
                    'Private Passport',

                'file_name' =>
                    basename(
                        $filePath,
                    ),

                'file_path' =>
                    $filePath,

                'file_type' =>
                    'PDF',

                'file_size' =>
                    100,

                'documents' => [
                    [
                        'file_name' =>
                            basename(
                                $filePath,
                            ),

                        'file_path' =>
                            $filePath,

                        'file_type' =>
                            'PDF',

                        'file_size' =>
                            100,
                    ],
                ],

                'status' =>
                    'Active',
            ]);
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