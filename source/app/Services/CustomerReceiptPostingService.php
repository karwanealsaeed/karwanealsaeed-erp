<?php

namespace App\Services;

use App\Models\AccountHead;
use App\Models\CustomerPaymentUpload;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CustomerReceiptPostingService
{
    private const TOLERANCE = 0.01;

    public function __construct(
        private readonly AccountingPeriodLockService $periodLockService,
    ) {
    }

    public function approve(
        CustomerPaymentUpload|int $receipt,
        int $userId,
    ): CustomerPaymentUpload {
        $receiptId = $receipt instanceof CustomerPaymentUpload
            ? (int) $receipt->getKey()
            : (int) $receipt;

        return DB::transaction(function () use (
            $receiptId,
            $userId
        ): CustomerPaymentUpload {
            $receipt = CustomerPaymentUpload::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($receiptId);

            if ($receipt->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Pending customer receipt approve ho sakti hai.',
                ]);
            }

            $this->assertReceiptPeriodOpen(
                receipt: $receipt,
                context: 'Customer Receipt Approval',
            );

            $this->assertNoPostedVoucher($receipt);

            $context = $this->postingContext(
                receipt: $receipt,
                requireOpenInvoice: true,
            );

            if (
                $receipt->receipt_type
                === 'Invoice Payment'
            ) {
                $this->validateInvoiceAmount(
                    $context['invoice'],
                    (float) $receipt->amount
                );
            }

            $voucherId = $this->createReceiptVoucher(
                receipt: $receipt,
                context: $context,
                userId: $userId,
            );

            $invoiceBalanceApplied = false;

            if (
                $receipt->receipt_type
                === 'Invoice Payment'
            ) {
                $this->applyInvoicePayment(
                    invoice: $context['invoice'],
                    amount: (float) $receipt->amount,
                );

                $invoiceBalanceApplied = true;
            }

            $receipt->updateQuietly([
                'status' => 'Approved',

                'approved_by' => $userId,
                'approved_at' => now(),

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,

                'account_voucher_id' => $voucherId,
                'invoice_balance_applied' =>
                    $invoiceBalanceApplied,

                'posted_by' => $userId,
                'posted_at' => now(),

                'voided_by' => null,
                'voided_at' => null,
                'void_reason' => null,
            ]);

            return $receipt->refresh();
        });
    }

    public function reject(
        CustomerPaymentUpload|int $receipt,
        int $userId,
        string $reason,
    ): CustomerPaymentUpload {
        $receiptId = $receipt instanceof CustomerPaymentUpload
            ? (int) $receipt->getKey()
            : (int) $receipt;

        return DB::transaction(function () use (
            $receiptId,
            $userId,
            $reason
        ): CustomerPaymentUpload {
            $receipt = CustomerPaymentUpload::query()
                ->lockForUpdate()
                ->findOrFail($receiptId);

            if ($receipt->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Pending customer receipt reject ho sakti hai.',
                ]);
            }

            $receipt->updateQuietly([
                'status' => 'Rejected',

                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => trim($reason),

                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $receipt->refresh();
        });
    }

    public function resubmit(
        CustomerPaymentUpload|int $receipt
    ): CustomerPaymentUpload {
        $receiptId = $receipt instanceof CustomerPaymentUpload
            ? (int) $receipt->getKey()
            : (int) $receipt;

        return DB::transaction(function () use (
            $receiptId
        ): CustomerPaymentUpload {
            $receipt = CustomerPaymentUpload::query()
                ->lockForUpdate()
                ->findOrFail($receiptId);

            if ($receipt->status !== 'Rejected') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Rejected receipt dobara submit ho sakti hai.',
                ]);
            }

            $receipt->updateQuietly([
                'status' => 'Pending',

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            return $receipt->refresh();
        });
    }

    /*
     * Legacy approved receipt:
     * status Approved hai magar voucher missing hai.
     *
     * $applyInvoiceBalance false safe default hai,
     * kyun ke purane workflow ne invoice already update
     * ki ho sakti hai.
     */
    public function postMissingApprovedReceipt(
        CustomerPaymentUpload|int $receipt,
        int $userId,
        bool $applyInvoiceBalance = false,
    ): CustomerPaymentUpload {
        $receiptId = $receipt instanceof CustomerPaymentUpload
            ? (int) $receipt->getKey()
            : (int) $receipt;

        return DB::transaction(function () use (
            $receiptId,
            $userId,
            $applyInvoiceBalance
        ): CustomerPaymentUpload {
            $receipt = CustomerPaymentUpload::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($receiptId);

            if ($receipt->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf legacy Approved receipt ka missing voucher post ho sakta hai.',
                ]);
            }

            $this->assertReceiptPeriodOpen(
                receipt: $receipt,
                context: 'Customer Receipt Missing GL Posting',
            );

            $this->assertNoPostedVoucher($receipt);

            $context = $this->postingContext(
                receipt: $receipt,
                requireOpenInvoice:
                    $applyInvoiceBalance,
            );

            $invoiceBalanceApplied =
                (bool) $receipt->invoice_balance_applied;

            if (
                $receipt->receipt_type
                    === 'Invoice Payment'
                && $applyInvoiceBalance
                && ! $invoiceBalanceApplied
            ) {
                $this->validateInvoiceAmount(
                    $context['invoice'],
                    (float) $receipt->amount
                );

                $this->applyInvoicePayment(
                    invoice: $context['invoice'],
                    amount: (float) $receipt->amount,
                );

                $invoiceBalanceApplied = true;
            }

            $voucherId = $this->createReceiptVoucher(
                receipt: $receipt,
                context: $context,
                userId: $userId,
            );

            $receipt->updateQuietly([
                'account_voucher_id' => $voucherId,

                'invoice_balance_applied' =>
                    $invoiceBalanceApplied,

                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            return $receipt->refresh();
        });
    }

    /*
     * Existing posted receipt ke wrong account heads
     * ko correct karta hai.
     *
     * Old voucher Cancelled hota hai aur naya balanced
     * voucher create hota hai. Invoice dobara update
     * nahi hoti.
     */
    public function correctPosting(
        CustomerPaymentUpload|int $receipt,
        int $userId,
        int $receiptAccountHeadId,
        int $contraAccountHeadId,
    ): CustomerPaymentUpload {
        $receiptId = $receipt instanceof CustomerPaymentUpload
            ? (int) $receipt->getKey()
            : (int) $receipt;

        return DB::transaction(function () use (
            $receiptId,
            $userId,
            $receiptAccountHeadId,
            $contraAccountHeadId
        ): CustomerPaymentUpload {
            $receipt = CustomerPaymentUpload::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($receiptId);

            if ($receipt->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Approved customer receipt ki GL posting correct ho sakti hai.',
                ]);
            }

            $voucher = $this->currentPostedVoucher(
                $receipt
            );

            if (! $voucher) {
                throw ValidationException::withMessages([
                    'account_voucher_id' =>
                        'Current Posted receipt voucher nahi mila.',
                ]);
            }

            $this->assertReceiptPeriodOpen(
                receipt: $receipt,
                context: 'Customer Receipt GL Correction',
                voucherDate:
                    $voucher->voucher_date
                    ?? null,
            );

            $this->assertVoucherNotReconciled(
                (int) $voucher->id
            );

            $context = $this->postingContext(
                receipt: $receipt,
                requireOpenInvoice: false,
                receiptAccountOverride:
                    $receiptAccountHeadId,
                contraAccountOverride:
                    $contraAccountHeadId,
            );

            $this->cancelVoucher(
                (int) $voucher->id
            );

            $voucherId = $this->createReceiptVoucher(
                receipt: $receipt,
                context: $context,
                userId: $userId,
            );

            $updates = [
                'receipt_account_head_id' =>
                    $receiptAccountHeadId,

                'account_voucher_id' =>
                    $voucherId,

                'posted_by' => $userId,
                'posted_at' => now(),
            ];

            if (
                $receipt->receipt_type
                === 'Invoice Payment'
            ) {
                $updates['receivable_account_head_id'] =
                    $contraAccountHeadId;

                $updates['advance_account_head_id'] =
                    null;
            } else {
                $updates['advance_account_head_id'] =
                    $contraAccountHeadId;

                $updates['receivable_account_head_id'] =
                    null;
            }

            $receipt->updateQuietly($updates);

            return $receipt->refresh();
        });
    }

    public function voidAndReverse(
        CustomerPaymentUpload|int $receipt,
        int $userId,
        string $reason,
    ): CustomerPaymentUpload {
        $receiptId = $receipt instanceof CustomerPaymentUpload
            ? (int) $receipt->getKey()
            : (int) $receipt;

        return DB::transaction(function () use (
            $receiptId,
            $userId,
            $reason
        ): CustomerPaymentUpload {
            $receipt = CustomerPaymentUpload::query()
                ->lockForUpdate()
                ->findOrFail($receiptId);

            if ($receipt->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Approved receipt void ho sakti hai.',
                ]);
            }

            $voucher = $this->currentPostedVoucher(
                $receipt
            );

            if (! $voucher) {
                throw ValidationException::withMessages([
                    'account_voucher_id' =>
                        'Posted receipt voucher nahi mila.',
                ]);
            }

            $this->assertReceiptPeriodOpen(
                receipt: $receipt,
                context: 'Customer Receipt Void and Reverse',
                voucherDate:
                    $voucher->voucher_date
                    ?? null,
            );

            $this->assertVoucherNotReconciled(
                (int) $voucher->id
            );

            if (
                $receipt->receipt_type
                    === 'Invoice Payment'
                && $receipt->invoice_balance_applied
            ) {
                $invoice = Invoice::query()
                    ->lockForUpdate()
                    ->find($receipt->invoice_id);

                if (! $invoice) {
                    throw ValidationException::withMessages([
                        'invoice_id' =>
                            'Linked invoice nahi mili; invoice balance safely reverse nahi ho sakta.',
                    ]);
                }

                $this->rollbackInvoicePayment(
                    invoice: $invoice,
                    amount: (float) $receipt->amount,
                );
            }

            $this->cancelVoucher(
                (int) $voucher->id
            );

            $receipt->updateQuietly([
                'status' => 'Cancelled',

                'voided_by' => $userId,
                'voided_at' => now(),
                'void_reason' => trim($reason),
            ]);

            return $receipt->refresh();
        });
    }

    private function assertReceiptPeriodOpen(
        CustomerPaymentUpload $receipt,
        string $context,
        string|\DateTimeInterface|null $voucherDate = null,
    ): void {
        $accountingDate =
            $voucherDate
            ?: $receipt->payment_date
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
        CustomerPaymentUpload $receipt,
        bool $requireOpenInvoice,
        ?int $receiptAccountOverride = null,
        ?int $contraAccountOverride = null,
    ): array {
        $amount = round(
            (float) $receipt->amount,
            2
        );

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Receipt amount zero se zyada honi chahiye.',
            ]);
        }

        if (! $receipt->customer) {
            throw ValidationException::withMessages([
                'customer_id' =>
                    'Customer record nahi mila.',
            ]);
        }

        if (
            ! in_array(
                $receipt->receipt_type,
                [
                    'Invoice Payment',
                    'Customer Advance',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'receipt_type' =>
                    'Valid receipt type select karein.',
            ]);
        }

        $receiptAccountId =
            $receiptAccountOverride
            ?: (int) $receipt->receipt_account_head_id;

        $receiptAccount = $this->validateAccountHead(
            accountHeadId: $receiptAccountId,
            field: 'receipt_account_head_id',
            label: 'Cash / Bank / Wallet Account',
        );

        if (
            $receipt->receipt_type
            === 'Invoice Payment'
        ) {
            if (! $receipt->invoice_id) {
                throw ValidationException::withMessages([
                    'invoice_id' =>
                        'Invoice Payment ke liye invoice select karein.',
                ]);
            }

            $invoice = Invoice::query()
                ->lockForUpdate()
                ->find($receipt->invoice_id);

            if (! $invoice) {
                throw ValidationException::withMessages([
                    'invoice_id' =>
                        'Selected invoice nahi mili.',
                ]);
            }

            if (
                (int) $invoice->customer_id
                !== (int) $receipt->customer_id
            ) {
                throw ValidationException::withMessages([
                    'invoice_id' =>
                        'Selected invoice is customer ki nahi hai.',
                ]);
            }

            if (
                $requireOpenInvoice
                && ! in_array(
                    $invoice->status,
                    [
                        'Unpaid',
                        'Partially Paid',
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'invoice_id' =>
                        'Selected invoice payment ke liye open nahi hai.',
                ]);
            }

            $contraAccountId =
                $contraAccountOverride
                ?: (int)
                    $receipt
                        ->receivable_account_head_id;

            $contraAccount =
                $this->validateAccountHead(
                    accountHeadId: $contraAccountId,
                    field:
                        'receivable_account_head_id',
                    label:
                        'Accounts Receivable Account',
                );

            if (
                $receiptAccount->id
                === $contraAccount->id
            ) {
                throw ValidationException::withMessages([
                    'receivable_account_head_id' =>
                        'Receipt aur Receivable account same nahi ho sakte.',
                ]);
            }

            return [
                'invoice' => $invoice,
                'receipt_account' =>
                    $receiptAccount,

                'contra_account' =>
                    $contraAccount,

                'source_type' =>
                    'Customer Invoice Receipt',
            ];
        }

        $contraAccountId =
            $contraAccountOverride
            ?: (int)
                $receipt->advance_account_head_id;

        $contraAccount = $this->validateAccountHead(
            accountHeadId: $contraAccountId,
            field: 'advance_account_head_id',
            label: 'Customer Advance Account',
        );

        if (
            $receiptAccount->id
            === $contraAccount->id
        ) {
            throw ValidationException::withMessages([
                'advance_account_head_id' =>
                    'Receipt aur Customer Advance account same nahi ho sakte.',
            ]);
        }

        return [
            'invoice' => null,
            'receipt_account' =>
                $receiptAccount,

            'contra_account' =>
                $contraAccount,

            'source_type' =>
                'Customer Advance Receipt',
        ];
    }

    private function validateInvoiceAmount(
        Invoice $invoice,
        float $amount,
    ): void {
        $availableBalance = round(
            (float) $invoice->balance_amount,
            2
        );

        if (
            $amount
            > $availableBalance
                + self::TOLERANCE
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Receipt amount invoice ke available balance se zyada hai.',
            ]);
        }
    }

    private function applyInvoicePayment(
        Invoice $invoice,
        float $amount,
    ): void {
        $totalAmount = round(
            (float) $invoice->total_amount,
            2
        );

        $newPaidAmount = round(
            (float) $invoice->paid_amount
            + $amount,
            2
        );

        if (
            $newPaidAmount
            > $totalAmount + self::TOLERANCE
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Invoice total se zyada receipt apply nahi ho sakti.',
            ]);
        }

        $newPaidAmount = min(
            $newPaidAmount,
            $totalAmount
        );

        $balanceAmount = round(
            max(
                0,
                $totalAmount - $newPaidAmount
            ),
            2
        );

        $invoice->updateQuietly([
            'paid_amount' => $newPaidAmount,
            'balance_amount' => $balanceAmount,

            'status' => $balanceAmount
                <= self::TOLERANCE
                    ? 'Paid'
                    : 'Partially Paid',
        ]);
    }

    private function rollbackInvoicePayment(
        Invoice $invoice,
        float $amount,
    ): void {
        if ($invoice->status === 'Cancelled') {
            throw ValidationException::withMessages([
                'invoice_id' =>
                    'Cancelled invoice ka receipt balance reverse nahi ho sakta.',
            ]);
        }

        $currentPaid = round(
            (float) $invoice->paid_amount,
            2
        );

        if (
            $amount
            > $currentPaid + self::TOLERANCE
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Invoice paid amount receipt reversal se kam hai.',
            ]);
        }

        $newPaidAmount = round(
            max(0, $currentPaid - $amount),
            2
        );

        $totalAmount = round(
            (float) $invoice->total_amount,
            2
        );

        $balanceAmount = round(
            max(
                0,
                $totalAmount - $newPaidAmount
            ),
            2
        );

        $status = $newPaidAmount
            <= self::TOLERANCE
                ? 'Unpaid'
                : 'Partially Paid';

        if (
            $balanceAmount
            <= self::TOLERANCE
        ) {
            $status = 'Paid';
        }

        $invoice->updateQuietly([
            'paid_amount' => $newPaidAmount,
            'balance_amount' => $balanceAmount,
            'status' => $status,
        ]);
    }

    private function createReceiptVoucher(
        CustomerPaymentUpload $receipt,
        array $context,
        int $userId,
    ): int {
        $amount = round(
            (float) $receipt->amount,
            2
        );

        $voucherNo = $this->nextVoucherNo(
            $receipt
        );

        $customerName =
            $receipt->customer?->name
            ?: 'Customer';

        $invoiceNo =
            $context['invoice']?->invoice_no;

        $description =
            $receipt->receipt_type
            . ' '
            . $receipt->upload_no
            . ' - '
            . $customerName;

        if (filled($invoiceNo)) {
            $description .= ' against '
                . $invoiceNo;
        }

        $referenceNo =
            $receipt->transaction_id
            ?: $receipt->reference_no
            ?: $receipt->upload_no;

        $voucherDate =
            $receipt->payment_date
                ? $receipt->payment_date
                    ->format('Y-m-d')
                : today()->format('Y-m-d');

        $timestamp = now();

        $voucherId = DB::table(
            'account_vouchers'
        )->insertGetId([
            'voucher_no' => $voucherNo,
            'voucher_date' => $voucherDate,
            'voucher_type' => 'Receipt Voucher',
            'reference_no' => $referenceNo,
            'party_name' => $customerName,
            'description' => $description,

            'total_debit' => $amount,
            'total_credit' => $amount,

            'status' => 'Posted',
            'created_by' => $userId,

            'source_model' =>
                CustomerPaymentUpload::class,

            'source_id' => $receipt->id,

            'source_type' =>
                $context['source_type'],

            'source_no' =>
                $receipt->upload_no,

            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $lines = [
            [
                'account_head_id' =>
                    $context[
                        'receipt_account'
                    ]->id,

                'description' =>
                    'Customer receipt received: '
                    . $receipt->upload_no,

                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_head_id' =>
                    $context[
                        'contra_account'
                    ]->id,

                'description' =>
                    $receipt->receipt_type
                    === 'Invoice Payment'
                        ? 'Accounts receivable settlement: '
                            . (
                                $invoiceNo
                                ?: $receipt->upload_no
                            )
                        : 'Customer advance received: '
                            . $receipt->upload_no,

                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        foreach ($lines as $index => $line) {
            $lineId = DB::table(
                'account_voucher_lines'
            )->insertGetId([
                'account_voucher_id' =>
                    $voucherId,

                'account_head_id' =>
                    $line['account_head_id'],

                'description' =>
                    $line['description'],

                'debit' => $line['debit'],
                'credit' => $line['credit'],

                'sort_order' => $index + 1,

                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DB::table('account_ledgers')->insert([
                'account_head_id' =>
                    $line['account_head_id'],

                'account_voucher_id' =>
                    $voucherId,

                'account_voucher_line_id' =>
                    $lineId,

                'entry_date' => $voucherDate,
                'voucher_no' => $voucherNo,
                'voucher_type' =>
                    'Receipt Voucher',

                'reference_no' => $referenceNo,

                'description' =>
                    $line['description'],

                'debit' => $line['debit'],
                'credit' => $line['credit'],

                'status' => 'Posted',

                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        return $voucherId;
    }

    private function nextVoucherNo(
        CustomerPaymentUpload $receipt
    ): string {
        $base = 'CRV-' . str_pad(
            (string) $receipt->id,
            7,
            '0',
            STR_PAD_LEFT
        );

        $baseExists = DB::table(
            'account_vouchers'
        )
            ->where('voucher_no', $base)
            ->exists();

        if (! $baseExists) {
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
                ->where(
                    'voucher_no',
                    $candidate
                )
                ->exists()
        );

        return $candidate;
    }

    private function assertNoPostedVoucher(
        CustomerPaymentUpload $receipt
    ): void {
        $query = DB::table('account_vouchers')
            ->where('status', 'Posted')
            ->where(function ($query) use (
                $receipt
            ): void {
                $query
                    ->where(function ($query) use (
                        $receipt
                    ): void {
                        $query
                            ->where(
                                'source_model',
                                CustomerPaymentUpload::class
                            )
                            ->where(
                                'source_id',
                                $receipt->id
                            );
                    });

                if ($receipt->account_voucher_id) {
                    $query->orWhere(
                        'id',
                        $receipt
                            ->account_voucher_id
                    );
                }
            });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'account_voucher_id' =>
                    'Is customer receipt ka Posted voucher already mojood hai.',
            ]);
        }
    }

    private function currentPostedVoucher(
        CustomerPaymentUpload $receipt
    ): ?object {
        if ($receipt->account_voucher_id) {
            $voucher = DB::table(
                'account_vouchers'
            )
                ->where(
                    'id',
                    $receipt->account_voucher_id
                )
                ->where('status', 'Posted')
                ->lockForUpdate()
                ->first();

            if ($voucher) {
                return $voucher;
            }
        }

        return DB::table('account_vouchers')
            ->where(
                'source_model',
                CustomerPaymentUpload::class
            )
            ->where('source_id', $receipt->id)
            ->where('status', 'Posted')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    private function assertVoucherNotReconciled(
        int $voucherId
    ): void {
        if (
            ! Schema::hasTable(
                'bank_statement_entries'
            )
        ) {
            return;
        }

        $ledgerIds = DB::table(
            'account_ledgers'
        )
            ->where(
                'account_voucher_id',
                $voucherId
            )
            ->pluck('id');

        if ($ledgerIds->isEmpty()) {
            return;
        }

        $isReconciled = DB::table(
            'bank_statement_entries'
        )
            ->whereIn(
                'matched_account_ledger_id',
                $ledgerIds
            )
            ->exists();

        if ($isReconciled) {
            throw ValidationException::withMessages([
                'account_voucher_id' =>
                    'Receipt voucher bank reconciliation me matched hai. Pehle statement entry unmatch karein.',
            ]);
        }
    }

    private function cancelVoucher(
        int $voucherId
    ): void {
        DB::table('account_vouchers')
            ->where('id', $voucherId)
            ->update([
                'status' => 'Cancelled',
                'updated_at' => now(),
            ]);

        DB::table('account_ledgers')
            ->where(
                'account_voucher_id',
                $voucherId
            )
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
                $field =>
                    $label . ' select karein.',
            ]);
        }

        $accountHead = AccountHead::query()
            ->whereKey((int) $accountHeadId)
            ->where('status', 'Active')
            ->first();

        if (! $accountHead) {
            throw ValidationException::withMessages([
                $field =>
                    $label
                    . ' active Account Head nahi hai.',
            ]);
        }

        return $accountHead;
    }
}