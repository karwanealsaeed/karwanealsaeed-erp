<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccountVoucher;
use App\Services\AccountingPeriodLockService;
use App\Services\AccountingPostingAuditService;
use App\Services\AccountingVoucherReversalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

final class AccountingVoucherReversalTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_reversal_schema_and_relations_exist(): void
    {
        $this->assertTrue(
            Schema::hasColumn(
                'account_vouchers',
                'reversal_of_voucher_id',
            ),
        );

        $this->assertTrue(
            Schema::hasColumn(
                'account_vouchers',
                'reversal_reason',
            ),
        );

        $original =
            $this->orphanVoucher(
                reference: 'PU-REV-SCHEMA',
            );

        $reversal =
            app(
                AccountingVoucherReversalService::class,
            )->reverse(
                original: $original,
                reason:
                    'Schema relation regression',
                reversalDate:
                    '2026-07-23',
            );

        $this->assertTrue(
            $reversal
                ->reversalOf
                ->is($original),
        );

        $this->assertTrue(
            $original
                ->fresh()
                ->reversal
                ->is($reversal),
        );
    }

    public function test_service_creates_balanced_mirrored_posted_reversal(): void
    {
        $original =
            $this->orphanVoucher(
                reference: 'PU-REV-MIRROR',
                amount: 100000,
            );

        $originalSnapshot =
            $this->voucherSnapshot(
                $original,
            );

        $reversal =
            app(
                AccountingVoucherReversalService::class,
            )->reverse(
                original: $original,
                reason:
                    'Confirmed UAT orphan',
                reversalDate:
                    '2026-07-23',
            );

        $this->assertSame(
            'Posted',
            $reversal->status,
        );

        $this->assertSame(
            $original->id,
            $reversal
                ->reversal_of_voucher_id,
        );

        $this->assertSame(
            'REV-'.$original->voucher_no,
            $reversal->reference_no,
        );

        $this->assertEqualsWithDelta(
            100000,
            (float) $reversal->total_debit,
            0.01,
        );

        $this->assertEqualsWithDelta(
            100000,
            (float) $reversal->total_credit,
            0.01,
        );

        $this->assertCount(
            2,
            $reversal->lines,
        );

        $this->assertCount(
            2,
            $reversal->ledgers,
        );

        $bankNet =
            DB::table('account_ledgers')
                ->whereIn(
                    'account_voucher_id',
                    [
                        $original->id,
                        $reversal->id,
                    ],
                )
                ->where(
                    'account_head_id',
                    $original
                        ->lines
                        ->firstWhere(
                            'debit',
                            '>',
                            0,
                        )
                        ->account_head_id,
                )
                ->selectRaw(
                    'COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) AS net',
                )
                ->value('net');

        $this->assertEqualsWithDelta(
            0,
            (float) $bankNet,
            0.01,
        );

        $this->assertEquals(
            $originalSnapshot,
            $this->voucherSnapshot(
                $original->fresh(),
            ),
        );
    }

    public function test_reversal_is_idempotent(): void
    {
        $original =
            $this->orphanVoucher(
                reference: 'PU-REV-IDEMPOTENT',
            );

        $service =
            app(
                AccountingVoucherReversalService::class,
            );

        $first =
            $service->reverse(
                original: $original,
                reason:
                    'First reversal',
                reversalDate:
                    '2026-07-23',
            );

        $second =
            $service->reverse(
                original: $original,
                reason:
                    'Repeated request',
                reversalDate:
                    '2026-07-23',
            );

        $this->assertSame(
            $first->id,
            $second->id,
        );

        $this->assertSame(
            1,
            AccountVoucher::query()
                ->where(
                    'reversal_of_voucher_id',
                    $original->id,
                )
                ->count(),
        );
    }

    public function test_audit_closes_only_valid_reversed_orphan(): void
    {
        $reversedOriginal =
            $this->orphanVoucher(
                reference: 'PU-REV-AUDIT-OK',
            );

        $unresolvedOriginal =
            $this->orphanVoucher(
                reference:
                    'PU-REV-AUDIT-MISSING',
            );

        app(
            AccountingVoucherReversalService::class,
        )->reverse(
            original: $reversedOriginal,
            reason:
                'Valid linked reversal',
            reversalDate:
                '2026-07-23',
        );

        $report =
            app(
                AccountingPostingAuditService::class,
            )->generate(
                dateFrom: '2026-07-01',
                dateTo: '2026-07-31',
                voucherStatus: 'Posted',
            );

        $reversedIssues =
            collect(
                $report['issue_rows'],
            )
                ->where(
                    'voucher_id',
                    $reversedOriginal->id,
                )
                ->pluck('issue_key');

        $unresolvedIssues =
            collect(
                $report['issue_rows'],
            )
                ->where(
                    'voucher_id',
                    $unresolvedOriginal->id,
                )
                ->pluck('issue_key');

        $this->assertNotContains(
            'source_record_missing',
            $reversedIssues,
        );

        $this->assertContains(
            'source_record_missing',
            $unresolvedIssues,
        );
    }

    public function test_locked_general_ledger_date_blocks_reversal(): void
    {
        $original =
            $this->orphanVoucher(
                reference: 'PU-REV-LOCKED',
            );

        $this->insertAccountingRow(
            'accounting_period_locks',
            [
                'name' =>
                    'Reversal Locked Date',
                'period_start' =>
                    '2026-07-23',
                'period_end' =>
                    '2026-07-23',
                'lock_scope' =>
                    AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
                'status' => 'Locked',
                'locked_by' => null,
                'locked_at' => now(),
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $this->expectException(
            ValidationException::class,
        );

        app(
            AccountingVoucherReversalService::class,
        )->reverse(
            original: $original,
            reason:
                'Must be blocked',
            reversalDate:
                '2026-07-23',
        );
    }

    private function orphanVoucher(
        string $reference,
        float $amount = 500,
    ): AccountVoucher {
        $bankHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
            );

        $incomeHead =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales',
            );

        $voucher =
            AccountVoucher::query()
                ->create([
                    'voucher_date' =>
                        '2026-07-13',
                    'voucher_type' =>
                        'Receipt',
                    'reference_no' =>
                        $reference,
                    'party_name' =>
                        'Test Orphan',
                    'description' =>
                        'Orphan source regression',
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'status' => 'Posted',
                    'source_model' =>
                        \App\Models\CustomerPaymentUpload::class,
                    'source_id' =>
                        999999,
                    'source_type' =>
                        'CustomerPaymentUpload',
                    'source_no' =>
                        $reference,
                ]);

        $voucher
            ->lines()
            ->create([
                'account_head_id' =>
                    $bankHead->id,
                'description' =>
                    'Orphan receipt',
                'debit' =>
                    $amount,
                'credit' => 0,
                'sort_order' => 1,
            ]);

        $voucher
            ->lines()
            ->create([
                'account_head_id' =>
                    $incomeHead->id,
                'description' =>
                    'Orphan income',
                'debit' => 0,
                'credit' =>
                    $amount,
                'sort_order' => 2,
            ]);

        $voucher->refreshTotals();
        $voucher->syncLedgerEntries();

        return $voucher
            ->fresh([
                'lines',
                'ledgers',
            ]);
    }

    private function voucherSnapshot(
        AccountVoucher $voucher,
    ): array {
        return [
            'header' =>
                DB::table('account_vouchers')
                    ->where(
                        'id',
                        $voucher->id,
                    )
                    ->first(),
            'lines' =>
                DB::table(
                    'account_voucher_lines',
                )
                    ->where(
                        'account_voucher_id',
                        $voucher->id,
                    )
                    ->orderBy('id')
                    ->get()
                    ->all(),
            'ledgers' =>
                DB::table('account_ledgers')
                    ->where(
                        'account_voucher_id',
                        $voucher->id,
                    )
                    ->orderBy('id')
                    ->get()
                    ->all(),
        ];
    }
}