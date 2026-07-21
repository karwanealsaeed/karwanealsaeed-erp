<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPortalDocumentsLedgerTest extends TestCase
{
    use RefreshDatabase;

    private static int $agentSequence = 0;

    public function test_documents_page_uses_eloquent_accessors_and_download_links(): void
    {
        $customer =
            $this->agent();

        $document =
            $this->document(
                $customer,
            );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.documents',
                ),
            )
            ->assertOk()
            ->assertSee(
                'Passport Copy',
            )
            ->assertSee(
                'Passport, Visa',
            )
            ->assertSee(
                'passport-copy.pdf',
            )
            ->assertSee(
                route(
                    'customer.portal.documents.download',
                    [
                        'customerDocument' =>
                            $document,

                        'fileIndex' =>
                            0,
                    ],
                ),
                false,
            );
    }

    public function test_portal_customer_can_download_own_document(): void
    {
        Storage::fake(
            'public',
        );

        $customer =
            $this->agent();

        $document =
            $this->document(
                $customer,
            );

        Storage::disk(
            'public',
        )->put(
            'customer-documents/passport-copy.pdf',
            'private portal document',
        );

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
            )
            ->assertOk()
            ->assertDownload(
                'passport-copy.pdf',
            );
    }

    public function test_portal_customer_cannot_download_another_customer_document(): void
    {
        Storage::fake(
            'public',
        );

        $first =
            $this->agent();

        $second =
            $this->agent();

        $document =
            $this->document(
                $second,
            );

        Storage::disk(
            'public',
        )->put(
            'customer-documents/passport-copy.pdf',
            'another customer document',
        );

        $this->withSession(
            $this->portalSession(
                $first,
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
            )
            ->assertForbidden();
    }

    public function test_guest_cannot_use_portal_document_download_route(): void
    {
        $customer =
            $this->agent();

        $document =
            $this->document(
                $customer,
            );

        $this->get(
            route(
                'customer.portal.documents.download',
                [
                    'customerDocument' =>
                        $document,

                    'fileIndex' =>
                        0,
                ],
            ),
        )
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            );
    }

    public function test_portal_and_admin_document_routes_keep_separate_security(): void
    {
        $portalRoute =
            Route::getRoutes()
                ->getByName(
                    'customer.portal.documents.download',
                );

        $adminRoute =
            Route::getRoutes()
                ->getByName(
                    'customer-documents.download',
                );

        $this->assertNotNull(
            $portalRoute,
        );

        $this->assertNotNull(
            $adminRoute,
        );

        $this->assertContains(
            'customer.portal',
            $portalRoute
                ->gatherMiddleware(),
        );

        $this->assertContains(
            'throttle:120,1',
            $portalRoute
                ->gatherMiddleware(),
        );

        $this->assertContains(
            'auth',
            $adminRoute
                ->gatherMiddleware(),
        );
    }

    public function test_ledger_uses_invoice_receipt_and_refund_as_authoritative_rows(): void
    {
        $customer =
            $this->agent();

        $this->createLedgerRecords(
            $customer,
        );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.ledger',
                ),
            )
            ->assertOk()
            ->assertViewHas(
                'closingBalance',
                700.0,
            )
            ->assertViewHas(
                'rows',
                function ($rows): bool {
                    return $rows->count() === 3
                        && $rows->pluck(
                            'transaction_key',
                        )->all()
                        === [
                            'invoice',
                            'invoice_receipt',
                            'invoice_refund',
                        ];
                },
            )
            ->assertSee(
                'PKR 700.00',
            )
            ->assertSee(
                'Invoice Payment Received',
            )
            ->assertSee(
                'Invoice Payment Refunded',
            );
    }

    public function test_dashboard_balance_uses_same_unified_statement(): void
    {
        $customer =
            $this->agent();

        $this->createLedgerRecords(
            $customer,
        );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.dashboard',
                ),
            )
            ->assertOk()
            ->assertViewHas(
                'ledgerBalance',
                700.0,
            );
    }

    public function test_legacy_portal_ledger_builder_is_removed(): void
    {
        $controller =
            file_get_contents(
                app_path(
                    'Http/Controllers/CustomerPortalController.php',
                ),
            );

        $documentsView =
            file_get_contents(
                resource_path(
                    'views/customer-portal/documents.blade.php',
                ),
            );

        $this->assertStringContainsString(
            'UnifiedCustomerStatementService',
            $controller,
        );

        $this->assertStringContainsString(
            'CustomerDocument::query()',
            $controller,
        );

        $this->assertStringNotContainsString(
            'private function customerLedgerRows',
            $controller,
        );

        $this->assertStringContainsString(
            'customer.portal.documents.download',
            $documentsView,
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
                    'Portal Agent '.$sequence,

                'mobile' =>
                    '0300000'.$sequence,

                'email' =>
                    'portal-agent-'
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
                    'Visa',
                ],

                'title' =>
                    'Passport Copy',

                'file_name' =>
                    'passport-copy.pdf',

                'file_path' =>
                    'customer-documents/passport-copy.pdf',

                'file_type' =>
                    'PDF',

                'file_size' =>
                    1024,

                'documents' => [
                    [
                        'file_name' =>
                            'passport-copy.pdf',

                        'file_path' =>
                            'customer-documents/passport-copy.pdf',

                        'file_type' =>
                            'PDF',

                        'file_size' =>
                            1024,
                    ],
                ],

                'status' =>
                    'Active',
            ]);
    }

    private function createLedgerRecords(
        Customer $customer,
    ): void {
        $timestamp =
            now();

        DB::table(
            'invoices',
        )->insert([
            'invoice_no' =>
                'INV-PORTAL-001',

            'customer_id' =>
                $customer->id,

            'invoice_date' =>
                today()->format(
                    'Y-m-d',
                ),

            'service_type' =>
                'Visa',

            'service_reference' =>
                'VISA-001',

            'sub_total' =>
                1000,

            'discount' =>
                0,

            'tax' =>
                0,

            'total_amount' =>
                1000,

            'paid_amount' =>
                400,

            'balance_amount' =>
                600,

            'status' =>
                'Partially Paid',

            'created_at' =>
                $timestamp,

            'updated_at' =>
                $timestamp,
        ]);

        DB::table(
            'customer_payment_uploads',
        )->insert([
            'customer_id' =>
                $customer->id,

            'upload_no' =>
                'PU-PORTAL-001',

            'service_type' =>
                'Visa',

            'receipt_type' =>
                'Invoice Payment',

            'reference_no' =>
                'INV-PORTAL-001',

            'amount' =>
                400,

            'payment_method' =>
                'Bank Transfer',

            'payment_date' =>
                today()->format(
                    'Y-m-d',
                ),

            'transaction_id' =>
                'TXN-PORTAL-001',

            'status' =>
                'Approved',

            'approved_at' =>
                $timestamp,

            'created_at' =>
                $timestamp,

            'updated_at' =>
                $timestamp,
        ]);

        DB::table(
            'refunds',
        )->insert([
            'refund_no' =>
                'REF-PORTAL-001',

            'customer_id' =>
                $customer->id,

            'refund_date' =>
                today()->format(
                    'Y-m-d',
                ),

            'service_type' =>
                'Visa',

            'service_reference' =>
                'INV-PORTAL-001',

            'refund_amount' =>
                100,

            'payment_method' =>
                'Bank Transfer',

            'reference_no' =>
                'REF-TXN-001',

            'status' =>
                'Paid',

            'reason' =>
                'Partial service refund',

            'refund_type' =>
                'Invoice Refund',

            'created_at' =>
                $timestamp,

            'updated_at' =>
                $timestamp,
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
        ];
    }
}