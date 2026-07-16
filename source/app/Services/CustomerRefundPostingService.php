<?php

namespace App\Services;

use App\Models\AccountHead;
use App\Models\CustomerPaymentUpload;
use App\Models\Invoice;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CustomerRefundPostingService
{
    private const TOLERANCE = 0.01;

    public function __construct(
        private readonly AccountingPeriodLockService $periodLockService,
    ) {
    }

    public function approve(
        Refund|int $refund,
        int $userId,
    ): Refund {
        $refundId = $refund instanceof Refund
            ? (int) $refund->getKey()
            : (int) $refund;

        return DB::transaction(function () use (
            $refundId,
            $userId
        ): Refund {
            $refund = Refund::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' => 'Sirf Pending refund approve ho sakta hai.',
                ]);
            }

            $this->assertRefundPeriodOpen(
                refund: $refund,
                context: 'Customer Refund Approval',
            );

            $this->assertNoPostedVoucher($refund);

            $context = $this->postingContext(
                refund: $refund,
                requireAvailableBalance: true,
            );

            $voucherId = $this->createRefundVoucher(
                refund: $refund,
                context: $context,
                userId: $userId,
            );

            $invoiceBalanceReversed = false;

            if ($refund->refund_type === 'Invoice Refund') {
                $this->reverseInvoicePayment(
                    invoice: $context['invoice'],
                    amount: (float) $refund->refund_amount,
                );

                $invoiceBalanceReversed = true;
            }

            $refund->updateQuietly([
                'status' => 'Approved',

                'approved_by' => $userId,
                'approved_at' => now(),

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,

                'account_voucher_id' => $voucherId,
                'invoice_balance_reversed' => $invoiceBalanceReversed,

                'posted_by' => $userId,
                'posted_at' => now(),

                'voided_by' => null,
                'voided_at' => null,
                'void_reason' => null,
            ]);

            return $refund->refresh();
        });
    }

    public function reject(
        Refund|int $refund,
        int $userId,
        string $reason,
    ): Refund {
        $refundId = $refund instanceof Refund
            ? (int) $refund->getKey()
            : (int) $refund;

        return DB::transaction(function () use (
            $refundId,
            $userId,
            $reason
        ): Refund {
            $refund = Refund::query()
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' => 'Sirf Pending refund reject ho sakta hai.',
                ]);
            }

            $refund->updateQuietly([
                'status' => 'Rejected',

                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => trim($reason),

                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $refund->refresh();
        });
    }

    public function resubmit(
        Refund|int $refund
    ): Refund {
        $refundId = $refund instanceof Refund
            ? (int) $refund->getKey()
            : (int) $refund;

        return DB::transaction(function () use (
            $refundId
        ): Refund {
            $refund = Refund::query()
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'Rejected') {
                throw ValidationException::withMessages([
                    'status' => 'Sirf Rejected refund dobara submit ho sakta hai.',
                ]);
            }

            $refund->updateQuietly([
                'status' => 'Pending',

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            return $refund->refresh();
        });
    }

    public function postMissingApprovedRefund(
        Refund|int $refund,
        int $userId,
        bool $reverseInvoiceBalance = false,
    ): Refund {
        $refundId = $refund instanceof Refund
            ? (int) $refund->getKey()
            : (int) $refund;

        return DB::transaction(function () use (
            $refundId,
            $userId,
            $reverseInvoiceBalance
        ): Refund {
            $refund = Refund::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'status' => 'Sirf legacy Approved refund ka missing GL post ho sakta hai.',
                ]);
            }

            $this->assertRefundPeriodOpen(
                refund: $refund,
                context: 'Customer Refund Missing GL Posting',
            );

            $this->assertNoPostedVoucher($refund);

            $context = $this->postingContext(
                refund: $refund,
                requireAvailableBalance: $reverseInvoiceBalance,
            );

            $invoiceBalanceReversed = (bool) $refund->invoice_balance_reversed;

            if (
                $refund->refund_type === 'Invoice Refund'
                && $reverseInvoiceBalance
                && ! $invoiceBalanceReversed
            ) {
                $this->reverseInvoicePayment(
                    invoice: $context['invoice'],
                    amount: (float) $refund->refund_amount,
                );

                $invoiceBalanceReversed = true;
            }

            $voucherId = $this->createRefundVoucher(
                refund: $refund,
                context: $context,
                userId: $userId,
            );

            $refund->updateQuietly([
                'account_voucher_id' => $voucherId,
                'invoice_balance_reversed' => $invoiceBalanceReversed,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            return $refund->refresh();
        });
    }

    public function correctPosting(
        Refund|int $refund,
        int $userId,
        int $refundAccountHeadId,
        int $contraAccountHeadId,
    ): Refund {
        $refundId = $refund instanceof Refund
            ? (int) $refund->getKey()
            : (int) $refund;

        return DB::transaction(function () use (
            $refundId,
            $userId,
            $refundAccountHeadId,
            $contraAccountHeadId
        ): Refund {
            $refund = Refund::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'status' => 'Sirf Approved refund ki GL posting correct ho sakti hai.',
                ]);
            }

            $voucher = $this->currentPostedVoucher($refund);

            if (! $voucher) {
                throw ValidationException::withMessages([
                    'account_voucher_id' => 'Current Posted refund voucher nahi mila.',
                ]);
            }

            $this->assertRefundPeriodOpen(
                refund: $refund,
                context: 'Customer Refund GL Correction',
                voucherDate:
                    $voucher->voucher_date
                    ?? null,
            );

            $this->assertVoucherNotReconciled(
                (int) $voucher->id
            );

            $context = $this->postingContext(
                refund: $refund,
                requireAvailableBalance: false,
                refundAccountOverride: $refundAccountHeadId,
                contraAccountOverride: $contraAccountHeadId,
            );

            $this->cancelVoucher(
                (int) $voucher->id
            );

            $voucherId = $this->createRefundVoucher(
                refund: $refund,
                context: $context,
                userId: $userId,
            );

            $updates = [
                'refund_account_head_id' => $refundAccountHeadId,
                'account_voucher_id' => $voucherId,
                'posted_by' => $userId,
                'posted_at' => now(),
            ];

            if ($refund->refund_type === 'Invoice Refund') {
                $updates['receivable_account_head_id'] = $contraAccountHeadId;
                $updates['advance_account_head_id'] = null;
            } else {
                $updates['advance_account_head_id'] = $contraAccountHeadId;
                $updates['receivable_account_head_id'] = null;
            }

            $refund->updateQuietly($updates);

            return $refund->refresh();
        });
    }

    public function voidAndReverse(
        Refund|int $refund,
        int $userId,
        string $reason,
    ): Refund {
        $refundId = $refund instanceof Refund
            ? (int) $refund->getKey()
            : (int) $refund;

        return DB::transaction(function () use (
            $refundId,
            $userId,
            $reason
        ): Refund {
            $refund = Refund::query()
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'status' => 'Sirf Approved refund void ho sakta hai.',
                ]);
            }

            $voucher = $this->currentPostedVoucher($refund);

            if (! $voucher) {
                throw ValidationException::withMessages([
                    'account_voucher_id' => 'Posted refund voucher nahi mila.',
                ]);
            }

            $this->assertRefundPeriodOpen(
                refund: $refund,
                context: 'Customer Refund Void and Reverse',
                voucherDate:
                    $voucher->voucher_date
                    ?? null,
            );

            $this->assertVoucherNotReconciled(
                (int) $voucher->id
            );

            if (
                $refund->refund_type === 'Invoice Refund'
                && $refund->invoice_balance_reversed
            ) {
                $invoice = Invoice::query()
                    ->lockForUpdate()
                    ->find($refund->invoice_id);

                if (! $invoice) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'Linked invoice nahi mili; invoice balance safely restore nahi ho sakta.',
                    ]);
                }

                $this->restoreInvoicePayment(
                    invoice: $invoice,
                    amount: (float) $refund->refund_amount,
                );
            }

            $this->cancelVoucher(
                (int) $voucher->id
            );

            $refund->updateQuietly([
                'status' => 'Cancelled',

                'voided_by' => $userId,
                'voided_at' => now(),
                'void_reason' => trim($reason),
            ]);

            return $refund->refresh();
        });
    }

    private function assertRefundPeriodOpen(
        Refund $refund,
        string $context,
        string|\DateTimeInterface|null $voucherDate = null,
    ): void {
        $accountingDate =
            $voucherDate
            ?: $refund->refund_date
            ?: today();

        $this->periodLockService
            ->assertDateIsOpen(
                date: $accountingDate,
                scope:
                    AccountingPeriodLockService::SCOPE_CUSTOMER,
                context: $context,
            );
    }

    private function postingContext(
        Refund $refund,
        bool $requireAvailableBalance,
        ?int $refundAccountOverride = null,
        ?int $contraAccountOverride = null,
    ): array {
        $amount = round(
            (float) $refund->refund_amount,
            2
        );

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'refund_amount' => 'Refund amount zero se zyada honi chahiye.',
            ]);
        }

        if (! $refund->customer) {
            throw ValidationException::withMessages([
                'customer_id' => 'Customer record nahi mila.',
            ]);
        }

        if (
            ! in_array(
                $refund->refund_type,
                [
                    'Invoice Refund',
                    'Customer Advance Refund',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'refund_type' => 'Valid refund type select karein.',
            ]);
        }

        $refundAccountId = $refundAccountOverride
            ?: (int) $refund->refund_account_head_id;

        $refundAccount = $this->validateAccountHead(
            accountHeadId: $refundAccountId,
            field: 'refund_account_head_id',
            label: 'Cash / Bank / Wallet Account',
        );

        $originalReceipt = null;

        if ($refund->customer_payment_upload_id) {
            $originalReceipt = CustomerPaymentUpload::query()
                ->find($refund->customer_payment_upload_id);

            if (! $originalReceipt) {
                throw ValidationException::withMessages([
                    'customer_payment_upload_id' => 'Original customer receipt nahi mili.',
                ]);
            }

            if (
                (int) $originalReceipt->customer_id
                !== (int) $refund->customer_id
            ) {
                throw ValidationException::withMessages([
                    'customer_payment_upload_id' => 'Original receipt selected customer ki nahi hai.',
                ]);
            }

            if ($originalReceipt->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'customer_payment_upload_id' => 'Sirf Approved customer receipt refund ho sakti hai.',
                ]);
            }

            $alreadyRefunded = round(
                (float) Refund::query()
                    ->where(
                        'customer_payment_upload_id',
                        $originalReceipt->id
                    )
                    ->where('status', 'Approved')
                    ->when(
                        $refund->exists,
                        fn ($query) => $query->where(
                            'id',
                            '!=',
                            $refund->id
                        )
                    )
                    ->sum('refund_amount'),
                2
            );

            $remainingRefundable = round(
                max(
                    0,
                    (float) $originalReceipt->amount
                    - $alreadyRefunded
                ),
                2
            );

            if (
                $requireAvailableBalance
                && $amount > $remainingRefundable + self::TOLERANCE
            ) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Refund original receipt ke remaining refundable amount se zyada hai.',
                ]);
            }
        }

        if ($refund->refund_type === 'Invoice Refund') {
            if (! $refund->invoice_id) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Invoice Refund ke liye invoice select karein.',
                ]);
            }

            $invoice = Invoice::query()
                ->lockForUpdate()
                ->find($refund->invoice_id);

            if (! $invoice) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Selected invoice nahi mili.',
                ]);
            }

            if (
                (int) $invoice->customer_id
                !== (int) $refund->customer_id
            ) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Selected invoice is customer ki nahi hai.',
                ]);
            }

            if ($invoice->status === 'Draft') {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Draft invoice refund nahi ho sakti.',
                ]);
            }

            if (
                $requireAvailableBalance
                && $amount > (float) $invoice->paid_amount + self::TOLERANCE
            ) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Refund invoice ke current paid amount se zyada hai.',
                ]);
            }

            if (
                $originalReceipt
                && $originalReceipt->receipt_type !== 'Invoice Payment'
            ) {
                throw ValidationException::withMessages([
                    'customer_payment_upload_id' => 'Invoice Refund ke liye Invoice Payment receipt select karein.',
                ]);
            }

            if (
                $originalReceipt
                && $originalReceipt->invoice_id
                && (int) $originalReceipt->invoice_id !== (int) $invoice->id
            ) {
                throw ValidationException::withMessages([
                    'customer_payment_upload_id' => 'Original receipt selected invoice se related nahi hai.',
                ]);
            }

            $contraAccountId = $contraAccountOverride
                ?: (int) $refund->receivable_account_head_id;

            $contraAccount = $this->validateAccountHead(
                accountHeadId: $contraAccountId,
                field: 'receivable_account_head_id',
                label: 'Accounts Receivable Account',
            );

            if ($refundAccount->id === $contraAccount->id) {
                throw ValidationException::withMessages([
                    'receivable_account_head_id' => 'Refund aur Accounts Receivable account same nahi ho sakte.',
                ]);
            }

            return [
                'invoice' => $invoice,
                'original_receipt' => $originalReceipt,
                'refund_account' => $refundAccount,
                'contra_account' => $contraAccount,
                'source_type' => 'Customer Invoice Refund',
            ];
        }

        if (! $originalReceipt) {
            throw ValidationException::withMessages([
                'customer_payment_upload_id' => 'Customer Advance Refund ke liye original advance receipt select karein.',
            ]);
        }

        if ($originalReceipt->receipt_type !== 'Customer Advance') {
            throw ValidationException::withMessages([
                'customer_payment_upload_id' => 'Customer Advance Refund ke liye Customer Advance receipt select karein.',
            ]);
        }

        $contraAccountId = $contraAccountOverride
            ?: (int) $refund->advance_account_head_id;

        $contraAccount = $this->validateAccountHead(
            accountHeadId: $contraAccountId,
            field: 'advance_account_head_id',
            label: 'Customer Advance Account',
        );

        if ($refundAccount->id === $contraAccount->id) {
            throw ValidationException::withMessages([
                'advance_account_head_id' => 'Refund aur Customer Advance account same nahi ho sakte.',
            ]);
        }

        return [
            'invoice' => null,
            'original_receipt' => $originalReceipt,
            'refund_account' => $refundAccount,
            'contra_account' => $contraAccount,
            'source_type' => 'Customer Advance Refund',
        ];
    }

    private function reverseInvoicePayment(
        Invoice $invoice,
        float $amount,
    ): void {
        $currentPaid = round(
            (float) $invoice->paid_amount,
            2
        );

        if ($amount > $currentPaid + self::TOLERANCE) {
            throw ValidationException::withMessages([
                'refund_amount' => 'Invoice paid amount refund se kam hai.',
            ]);
        }

        $newPaid = round(
            max(0, $currentPaid - $amount),
            2
        );

        $total = round(
            (float) $invoice->total_amount,
            2
        );

        $balance = round(
            max(0, $total - $newPaid),
            2
        );

        $status = $newPaid <= self::TOLERANCE
            ? 'Unpaid'
            : 'Partially Paid';

        if ($balance <= self::TOLERANCE) {
            $status = 'Paid';
        }

        $invoice->updateQuietly([
            'paid_amount' => $newPaid,
            'balance_amount' => $balance,
            'status' => $status,
        ]);
    }

    private function restoreInvoicePayment(
        Invoice $invoice,
        float $amount,
    ): void {
        $total = round(
            (float) $invoice->total_amount,
            2
        );

        $newPaid = round(
            (float) $invoice->paid_amount
            + $amount,
            2
        );

        if ($newPaid > $total + self::TOLERANCE) {
            throw ValidationException::withMessages([
                'refund_amount' => 'Refund void karne se invoice paid amount total se zyada ho jayega.',
            ]);
        }

        $newPaid = min($newPaid, $total);

        $balance = round(
            max(0, $total - $newPaid),
            2
        );

        $status = $balance <= self::TOLERANCE
            ? 'Paid'
            : 'Partially Paid';

        if ($newPaid <= self::TOLERANCE) {
            $status = 'Unpaid';
        }

        $invoice->updateQuietly([
            'paid_amount' => $newPaid,
            'balance_amount' => $balance,
            'status' => $status,
        ]);
    }

    private function createRefundVoucher(
        Refund $refund,
        array $context,
        int $userId,
    ): int {
        $amount = round(
            (float) $refund->refund_amount,
            2
        );

        $voucherNo = $this->nextVoucherNo($refund);

        $customerName = $refund->customer?->name
            ?: 'Customer';

        $invoiceNo = $context['invoice']?->invoice_no;

        $description = $refund->refund_type
            . ' '
            . $refund->refund_no
            . ' - '
            . $customerName;

        if (filled($invoiceNo)) {
            $description .= ' against ' . $invoiceNo;
        }

        $referenceNo = $refund->service_reference
            ?: $refund->refund_no;

        $voucherDate = $refund->refund_date
            ? $refund->refund_date->format('Y-m-d')
            : today()->format('Y-m-d');

        $timestamp = now();

        $voucherId = DB::table('account_vouchers')
            ->insertGetId([
                'voucher_no' => $voucherNo,
                'voucher_date' => $voucherDate,
                'voucher_type' => 'Payment Voucher',
                'reference_no' => $referenceNo,
                'party_name' => $customerName,
                'description' => $description,

                'total_debit' => $amount,
                'total_credit' => $amount,

                'status' => 'Posted',
                'created_by' => $userId,

                'source_model' => Refund::class,
                'source_id' => $refund->id,
                'source_type' => $context['source_type'],
                'source_no' => $refund->refund_no,

                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        $lines = [
            [
                'account_head_id' => $context['contra_account']->id,
                'description' => $refund->refund_type === 'Invoice Refund'
                    ? 'Accounts receivable reopened: ' . ($invoiceNo ?: $refund->refund_no)
                    : 'Customer advance refunded: ' . $refund->refund_no,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_head_id' => $context['refund_account']->id,
                'description' => 'Customer refund paid: ' . $refund->refund_no,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        foreach ($lines as $index => $line) {
            $lineId = DB::table('account_voucher_lines')
                ->insertGetId([
                    'account_voucher_id' => $voucherId,
                    'account_head_id' => $line['account_head_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'sort_order' => $index + 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

            DB::table('account_ledgers')->insert([
                'account_head_id' => $line['account_head_id'],
                'account_voucher_id' => $voucherId,
                'account_voucher_line_id' => $lineId,
                'entry_date' => $voucherDate,
                'voucher_no' => $voucherNo,
                'voucher_type' => 'Payment Voucher',
                'reference_no' => $referenceNo,
                'description' => $line['description'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'status' => 'Posted',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        return $voucherId;
    }

    private function nextVoucherNo(Refund $refund): string
    {
        $base = 'RFV-' . str_pad(
            (string) $refund->id,
            7,
            '0',
            STR_PAD_LEFT
        );

        if (
            ! DB::table('account_vouchers')
                ->where('voucher_no', $base)
                ->exists()
        ) {
            return $base;
        }

        $sequence = 1;

        do {
            $candidate = $base
                . '-C'
                . str_pad(
                    (string) $sequence,
                    2,
                    '0',
                    STR_PAD_LEFT
                );

            $sequence++;
        } while (
            DB::table('account_vouchers')
                ->where('voucher_no', $candidate)
                ->exists()
        );

        return $candidate;
    }

    private function assertNoPostedVoucher(Refund $refund): void
    {
        $query = DB::table('account_vouchers')
            ->where('status', 'Posted')
            ->where(function ($query) use ($refund): void {
                $query->where(function ($query) use ($refund): void {
                    $query
                        ->where('source_model', Refund::class)
                        ->where('source_id', $refund->id);
                });

                if ($refund->account_voucher_id) {
                    $query->orWhere(
                        'id',
                        $refund->account_voucher_id
                    );
                }
            });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'account_voucher_id' => 'Is refund ka Posted voucher already mojood hai.',
            ]);
        }
    }

    private function currentPostedVoucher(
        Refund $refund
    ): ?object {
        if ($refund->account_voucher_id) {
            $voucher = DB::table('account_vouchers')
                ->where('id', $refund->account_voucher_id)
                ->where('status', 'Posted')
                ->lockForUpdate()
                ->first();

            if ($voucher) {
                return $voucher;
            }
        }

        return DB::table('account_vouchers')
            ->where('source_model', Refund::class)
            ->where('source_id', $refund->id)
            ->where('status', 'Posted')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    private function assertVoucherNotReconciled(
        int $voucherId
    ): void {
        if (! Schema::hasTable('bank_statement_entries')) {
            return;
        }

        $ledgerIds = DB::table('account_ledgers')
            ->where('account_voucher_id', $voucherId)
            ->pluck('id');

        if ($ledgerIds->isEmpty()) {
            return;
        }

        $matched = DB::table('bank_statement_entries')
            ->whereIn(
                'matched_account_ledger_id',
                $ledgerIds
            )
            ->exists();

        if ($matched) {
            throw ValidationException::withMessages([
                'account_voucher_id' => 'Refund voucher bank reconciliation me matched hai. Pehle statement entry unmatch karein.',
            ]);
        }
    }

    private function cancelVoucher(int $voucherId): void
    {
        DB::table('account_vouchers')
            ->where('id', $voucherId)
            ->update([
                'status' => 'Cancelled',
                'updated_at' => now(),
            ]);

        DB::table('account_ledgers')
            ->where('account_voucher_id', $voucherId)
            ->update([
                'status' => 'Cancelled',
                'updated_at' => now(),
            ]);
    }

    private function validateAccountHead(
        int|string|null $accountHeadId,
        string $field,
        string $label,
    ): AccountHead {
        if (! $accountHeadId) {
            throw ValidationException::withMessages([
                $field => $label . ' select karein.',
            ]);
        }

        $accountHead = AccountHead::query()
            ->whereKey((int) $accountHeadId)
            ->where('status', 'Active')
            ->first();

        if (! $accountHead) {
            throw ValidationException::withMessages([
                $field => $label . ' active Account Head nahi hai.',
            ]);
        }

        return $accountHead;
    }
}