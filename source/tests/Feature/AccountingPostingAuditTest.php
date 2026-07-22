<?php

namespace Tests\Feature;

use App\Services\AccountingPostingAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class AccountingPostingAuditTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_clean_posting_has_no_issues_and_missing_ledgers_are_detected(): void
    {
        $debitAccount =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash'
            );

        $creditAccount =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales'
            );

        $voucher =
            $this->createPostedAccountingVoucher(
                debitAccount: $debitAccount,
                creditAccount: $creditAccount,
                amount: 650,
                voucherDate: '2026-07-10'
            );

        $service =
            app(
                AccountingPostingAuditService::class
            );

        $clean =
            $service->generate(
                dateFrom: '2026-07-01',
                dateTo: '2026-07-31',
                voucherStatus: 'Posted',
                severity: 'all',
                issueType: 'all'
            );

        $this->assertSame(
            [],
            $clean['issue_rows']
        );

        DB::table('account_ledgers')
            ->where(
                'account_voucher_id',
                $voucher->id
            )
            ->delete();

        $broken =
            $service->generate(
                dateFrom: '2026-07-01',
                dateTo: '2026-07-31',
                voucherStatus: 'Posted',
                severity: 'all',
                issueType: 'all'
            );

        $issueKeys =
            collect($broken['issue_rows'])
                ->pluck('issue_key')
                ->all();

        $this->assertContains(
            'missing_ledgers',
            $issueKeys
        );
    }
}