<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

final class BankAccountAuditPrivacyTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_bank_account_creation_is_audited_without_exposing_account_number_or_iban(): void
    {
        $admin =
            User::factory()->create([
                'role' => 'Admin',
                'is_active' => true,
            ]);

        $this->actingAs($admin);

        $bankHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-BANK-PRIV-001',
                    'name' => 'Privacy Bank Head',
                ],
            );

        $accountNumber =
            '1234567890123';

        $iban =
            'PK00TEST0000000000009876';

        $bank =
            BankAccount::query()->create([
                'account_head_id' =>
                    $bankHead->id,
                'name' =>
                    'Privacy Test Bank',
                'bank_name' =>
                    'Privacy Bank Limited',
                'account_title' =>
                    'Privacy Account',
                'account_number' =>
                    $accountNumber,
                'iban' =>
                    $iban,
                'branch' =>
                    'Privacy Branch',
                'branch_code' =>
                    '1001',
                'swift_code' =>
                    'PRIVPKKA',
                'currency' =>
                    'PKR',
                'opening_date' =>
                    '2025-02-01',
                'opening_balance' =>
                    0,
                'status' =>
                    'Active',
                'notes' =>
                    null,
            ]);

        $log =
            ActivityLog::query()
                ->where(
                    'subject_type',
                    BankAccount::class,
                )
                ->where(
                    'subject_id',
                    $bank->id,
                )
                ->where(
                    'action',
                    'Created',
                )
                ->latest('id')
                ->firstOrFail();

        $payload =
            json_encode(
                $log->new_values,
                JSON_UNESCAPED_SLASHES,
            );

        $this->assertIsString($payload);
        $this->assertStringNotContainsString(
            $accountNumber,
            $payload,
        );
        $this->assertStringNotContainsString(
            $iban,
            $payload,
        );
        $this->assertStringContainsString(
            '[REDACTED:0123]',
            $payload,
        );
        $this->assertStringContainsString(
            '[REDACTED:9876]',
            $payload,
        );
    }

    public function test_bank_account_updates_redact_old_and_new_sensitive_values(): void
    {
        $admin =
            User::factory()->create([
                'role' => 'Admin',
                'is_active' => true,
            ]);

        $this->actingAs($admin);

        $bankHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-BANK-PRIV-002',
                    'name' => 'Privacy Update Bank Head',
                ],
            );

        $oldAccountNumber =
            '1111222233334444';

        $oldIban =
            'PK00TEST0000000000001111';

        $bank =
            BankAccount::query()->create([
                'account_head_id' =>
                    $bankHead->id,
                'name' =>
                    'Privacy Update Bank',
                'bank_name' =>
                    'Privacy Bank Limited',
                'account_title' =>
                    'Privacy Update Account',
                'account_number' =>
                    $oldAccountNumber,
                'iban' =>
                    $oldIban,
                'branch' =>
                    'Privacy Branch',
                'branch_code' =>
                    '1002',
                'swift_code' =>
                    'PRIVPKKA',
                'currency' =>
                    'PKR',
                'opening_date' =>
                    '2025-02-01',
                'opening_balance' =>
                    0,
                'status' =>
                    'Active',
                'notes' =>
                    null,
            ]);

        $newAccountNumber =
            '5555666677778888';

        $newIban =
            'PK00TEST0000000000009999';

        $bank->update([
            'account_number' =>
                $newAccountNumber,
            'iban' =>
                $newIban,
        ]);

        $log =
            ActivityLog::query()
                ->where(
                    'subject_type',
                    BankAccount::class,
                )
                ->where(
                    'subject_id',
                    $bank->id,
                )
                ->where(
                    'action',
                    'Updated',
                )
                ->latest('id')
                ->firstOrFail();

        $oldPayload =
            json_encode(
                $log->old_values,
                JSON_UNESCAPED_SLASHES,
            );

        $newPayload =
            json_encode(
                $log->new_values,
                JSON_UNESCAPED_SLASHES,
            );

        $this->assertIsString($oldPayload);
        $this->assertIsString($newPayload);

        foreach (
            [
                $oldAccountNumber,
                $oldIban,
                $newAccountNumber,
                $newIban,
            ]
            as $sensitiveValue
        ) {
            $this->assertStringNotContainsString(
                $sensitiveValue,
                $oldPayload,
            );

            $this->assertStringNotContainsString(
                $sensitiveValue,
                $newPayload,
            );
        }

        $this->assertStringContainsString(
            '[REDACTED:4444]',
            $oldPayload,
        );
        $this->assertStringContainsString(
            '[REDACTED:1111]',
            $oldPayload,
        );
        $this->assertStringContainsString(
            '[REDACTED:8888]',
            $newPayload,
        );
        $this->assertStringContainsString(
            '[REDACTED:9999]',
            $newPayload,
        );
    }
}