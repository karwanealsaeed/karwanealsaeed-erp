<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionStoragePrivacyTest extends TestCase
{
    public function test_payment_receipt_privacy_migration_sweeps_all_legacy_directories(): void
    {
        $source =
            file_get_contents(
                database_path(
                    'migrations/2026_07_21_143000_move_orphan_customer_payment_uploads_to_private_storage.php',
                ),
            );

        $this->assertStringContainsString(
            "'customer-payment-uploads'",
            $source,
        );

        $this->assertStringContainsString(
            "'customer/payment-receipts'",
            $source,
        );

        $this->assertStringContainsString(
            '->allFiles(',
            $source,
        );

        $this->assertStringContainsString(
            '->readStream(',
            $source,
        );

        $this->assertStringContainsString(
            '->size(',
            $source,
        );

        $this->assertStringContainsString(
            'Storage::disk(',
            $source,
        );

        $this->assertStringContainsString(
            "'local'",
            $source,
        );

        $this->assertStringContainsString(
            "'public'",
            $source,
        );

        $this->assertStringContainsString(
            'app()->runningUnitTests()',
            $source,
        );

        $this->assertStringContainsString(
            'public function down(): void',
            $source,
        );
    }

    public function test_sensitive_customer_payment_receipts_are_absent_from_public_storage(): void
    {
        $publicFiles = array_values(
            array_unique(
                array_merge(
                    Storage::disk(
                        'public',
                    )->allFiles(
                        'customer-payment-uploads',
                    ),
                    Storage::disk(
                        'public',
                    )->allFiles(
                        'customer/payment-receipts',
                    ),
                ),
            ),
        );

        $this->assertSame(
            [],
            $publicFiles,
            'Sensitive payment receipts must never remain on the public disk.',
        );
    }
}