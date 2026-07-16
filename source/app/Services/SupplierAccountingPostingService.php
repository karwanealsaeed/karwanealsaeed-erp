<?php

namespace App\Services;

use App\Models\AccountHead;
use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierAccountingPostingService
{
    public function approveBill(
        SupplierBill|int $bill,
        int $userId,
    ): SupplierBill {
        $billId = $bill instanceof SupplierBill
            ? (int) $bill->getKey()
            : (int) $bill;

        return DB::transaction(function () use (
            $billId,
            $userId
        ): SupplierBill {
            $bill = SupplierBill::query()
                ->with('supplier')
                ->lockForUpdate()
                ->findOrFail($billId);

            if ($bill->approval_status !== 'Pending') {
                throw ValidationException::withMessages([
                    'approval_status' =>
                        'Sirf Pending supplier bill approve ho sakta hai.',
                ]);
            }

            if ((float) $bill->total_amount <= 0) {
                throw ValidationException::withMessages([
                    'total_amount' =>
                        'Supplier bill total zero se zyada hona chahiye.',
                ]);
            }

            $this->validateAccountHead(
                $bill->expense_account_head_id,
                'expense_account_head_id',
                'Purchase / Expense Account'
            );

            $this->validateAccountHead(
                $bill->payable_account_head_id,
                'payable_account_head_id',
                'Supplier Payable Account'
            );

            if (
                (int) $bill->expense_account_head_id
                === (int) $bill->payable_account_head_id
            ) {
                throw ValidationException::withMessages([
                    'payable_account_head_id' =>
                        'Expense aur Payable account same nahi ho sakte.',
                ]);
            }

            $voucherId = $this->postBillVoucher(
                $bill,
                $userId
            );

            $bill->updateQuietly([
                'approval_status' => 'Approved',
                'status' => (float) $bill->paid_amount > 0
                    ? 'Partially Paid'
                    : 'Unpaid',

                'approved_by' => $userId,
                'approved_at' => now(),

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,

                'account_voucher_id' => $voucherId,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            return $bill->refresh();
        });
    }

    public function rejectBill(
        SupplierBill|int $bill,
        int $userId,
        string $reason,
    ): SupplierBill {
        $billId = $bill instanceof SupplierBill
            ? (int) $bill->getKey()
            : (int) $bill;

        return DB::transaction(function () use (
            $billId,
            $userId,
            $reason
        ): SupplierBill {
            $bill = SupplierBill::query()
                ->lockForUpdate()
                ->findOrFail($billId);

            if ($bill->approval_status !== 'Pending') {
                throw ValidationException::withMessages([
                    'approval_status' =>
                        'Sirf Pending supplier bill reject ho sakta hai.',
                ]);
            }

            $bill->updateQuietly([
                'approval_status' => 'Rejected',
                'status' => 'Draft',

                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => trim($reason),

                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $bill->refresh();
        });
    }

    public function resubmitBill(
        SupplierBill|int $bill
    ): SupplierBill {
        $billId = $bill instanceof SupplierBill
            ? (int) $bill->getKey()
            : (int) $bill;

        return DB::transaction(function () use (
            $billId
        ): SupplierBill {
            $bill = SupplierBill::query()
                ->lockForUpdate()
                ->findOrFail($billId);

            if ($bill->approval_status !== 'Rejected') {
                throw ValidationException::withMessages([
                    'approval_status' =>
                        'Sirf Rejected bill dobara submit ho sakta hai.',
                ]);
            }

            $bill->updateQuietly([
                'approval_status' => 'Pending',
                'status' => 'Draft',

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            return $bill->refresh();
        });
    }

    public function cancelBill(
        SupplierBill|int $bill,
        int $userId,
    ): SupplierBill {
        $billId = $bill instanceof SupplierBill
            ? (int) $bill->getKey()
            : (int) $bill;

        return DB::transaction(function () use (
            $billId,
            $userId
        ): SupplierBill {
            $bill = SupplierBill::query()
                ->lockForUpdate()
                ->findOrFail($billId);

            if ($bill->approval_status !== 'Approved') {
                throw ValidationException::withMessages([
                    'approval_status' =>
                        'Sirf Approved supplier bill cancel ho sakta hai.',
                ]);
            }

            if ((float) $bill->paid_amount > 0) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Payment wale supplier bill ko cancel nahi kiya ja sakta.',
                ]);
            }

            if ($bill->account_voucher_id) {
                $this->reverseVoucher(
                    (int) $bill->account_voucher_id
                );
            }

            $bill->updateQuietly([
                'approval_status' => 'Cancelled',
                'status' => 'Cancelled',
                'posted_by' => $userId,
            ]);

            return $bill->refresh();
        });
    }

    public function approvePayment(
        SupplierPayment|int $payment,
        int $userId,
    ): SupplierPayment {
        $paymentId = $payment instanceof SupplierPayment
            ? (int) $payment->getKey()
            : (int) $payment;

        return DB::transaction(function () use (
            $paymentId,
            $userId
        ): SupplierPayment {
            $payment = SupplierPayment::query()
                ->lockForUpdate()
                ->findOrFail($paymentId);

            $bill = SupplierBill::query()
                ->with('supplier')
                ->lockForUpdate()
                ->findOrFail($payment->supplier_bill_id);

            if ($payment->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Pending supplier payment approve ho sakti hai.',
                ]);
            }

            if ($bill->approval_status !== 'Approved') {
                throw ValidationException::withMessages([
                    'supplier_bill_id' =>
                        'Supplier bill pehle approve hona zaroori hai.',
                ]);
            }

            if (
                in_array(
                    $bill->status,
                    ['Draft', 'Paid', 'Cancelled'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'supplier_bill_id' =>
                        'Selected bill payment receive karne ke liye open nahi hai.',
                ]);
            }

            $this->validateAccountHead(
                $bill->payable_account_head_id,
                'payable_account_head_id',
                'Supplier Payable Account'
            );

            $this->validateAccountHead(
                $payment->payment_account_head_id,
                'payment_account_head_id',
                'Cash / Bank Account'
            );

            if (
                (int) $bill->payable_account_head_id
                === (int) $payment->payment_account_head_id
            ) {
                throw ValidationException::withMessages([
                    'payment_account_head_id' =>
                        'Payable aur Payment account same nahi ho sakte.',
                ]);
            }

$alreadyApproved = round(
    (float) SupplierPayment::query()
        ->where(
            'supplier_bill_id',
            $bill->id
        )
        ->where('status', 'Approved')
        ->where(
            'id',
            '!=',
            $payment->id
        )
        ->sum('amount'),
    2
);

            $availableBalance = round(
                (float) $bill->total_amount
                - $alreadyApproved,
                2
            );

            if ((float) $payment->amount > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Payment supplier bill ke available balance se zyada hai.',
                ]);
            }

            if (! $bill->account_voucher_id) {
                $billVoucherId = $this->postBillVoucher(
                    $bill,
                    $userId
                );

                $bill->updateQuietly([
                    'account_voucher_id' => $billVoucherId,
                    'posted_by' => $userId,
                    'posted_at' => now(),
                ]);
            }

            $voucherId = $this->postPaymentVoucher(
                $payment,
                $bill,
                $userId
            );

            $payment->updateQuietly([
                'status' => 'Approved',

                'approved_by' => $userId,
                'approved_at' => now(),

                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,

                'account_voucher_id' => $voucherId,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            $totalPaid = round(
                $alreadyApproved
                + (float) $payment->amount,
                2
            );

            $balance = round(
                max(
                    0,
                    (float) $bill->total_amount
                    - $totalPaid
                ),
                2
            );

            $bill->updateQuietly([
                'paid_amount' => $totalPaid,
                'balance_amount' => $balance,
                'status' => $balance <= 0
                    ? 'Paid'
                    : 'Partially Paid',
            ]);

            return $payment->refresh();
        });
    }

    public function rejectPayment(
        SupplierPayment|int $payment,
        int $userId,
        string $reason,
    ): SupplierPayment {
        $paymentId = $payment instanceof SupplierPayment
            ? (int) $payment->getKey()
            : (int) $payment;

        return DB::transaction(function () use (
            $paymentId,
            $userId,
            $reason
        ): SupplierPayment {
            $payment = SupplierPayment::query()
                ->lockForUpdate()
                ->findOrFail($paymentId);

            if ($payment->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Pending payment reject ho sakti hai.',
                ]);
            }

            $payment->updateQuietly([
                'status' => 'Rejected',

                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => trim($reason),

                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $payment->refresh();
        });
    }

    public function cancelPayment(
        SupplierPayment|int $payment
    ): SupplierPayment {
        $paymentId = $payment instanceof SupplierPayment
            ? (int) $payment->getKey()
            : (int) $payment;

        return DB::transaction(function () use (
            $paymentId
        ): SupplierPayment {
            $payment = SupplierPayment::query()
                ->lockForUpdate()
                ->findOrFail($paymentId);

            if ($payment->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Sirf Pending supplier payment cancel ho sakti hai.',
                ]);
            }

            $payment->updateQuietly([
                'status' => 'Cancelled',
            ]);

            return $payment->refresh();
        });
    }

    private function postBillVoucher(
        SupplierBill $bill,
        int $userId,
    ): int {
        $existingVoucherId = $this->findExistingVoucher(
            SupplierBill::class,
            (int) $bill->id
        );

        if ($existingVoucherId) {
            return $existingVoucherId;
        }

        $voucherNo = 'SBV-' . str_pad(
            (string) $bill->id,
            7,
            '0',
            STR_PAD_LEFT
        );

        $supplierName = $bill->supplier?->display_name
            ?: $bill->supplier?->name
            ?: 'Supplier';

        $description = 'Supplier Bill '
            . $bill->bill_no
            . ' - '
            . $supplierName;

        return $this->createVoucher(
            voucherNo: $voucherNo,
            voucherDate: $bill->bill_date->format('Y-m-d'),
            voucherType: 'Journal Voucher',
            referenceNo: $bill->supplier_invoice_no
                ?: $bill->reference_no
                ?: $bill->bill_no,

            partyName: $supplierName,
            description: $description,
            sourceModel: SupplierBill::class,
            sourceId: (int) $bill->id,
            sourceType: 'Supplier Bill',
            sourceNo: $bill->bill_no,
            userId: $userId,

            lines: [
                [
                    'account_head_id' =>
                        (int) $bill->expense_account_head_id,

                    'description' =>
                        'Supplier purchase / expense: '
                        . $bill->bill_no,

                    'debit' => (float) $bill->total_amount,
                    'credit' => 0,
                ],
                [
                    'account_head_id' =>
                        (int) $bill->payable_account_head_id,

                    'description' =>
                        'Supplier payable: '
                        . $bill->bill_no,

                    'debit' => 0,
                    'credit' => (float) $bill->total_amount,
                ],
            ]
        );
    }

    private function postPaymentVoucher(
        SupplierPayment $payment,
        SupplierBill $bill,
        int $userId,
    ): int {
        $existingVoucherId = $this->findExistingVoucher(
            SupplierPayment::class,
            (int) $payment->id
        );

        if ($existingVoucherId) {
            return $existingVoucherId;
        }

        $voucherNo = 'SPV-' . str_pad(
            (string) $payment->id,
            7,
            '0',
            STR_PAD_LEFT
        );

        $supplierName = $bill->supplier?->display_name
            ?: $bill->supplier?->name
            ?: 'Supplier';

        $description = 'Supplier Payment '
            . $payment->payment_no
            . ' against '
            . $bill->bill_no;

        return $this->createVoucher(
            voucherNo: $voucherNo,
            voucherDate: $payment->payment_date->format('Y-m-d'),
            voucherType: 'Payment Voucher',
            referenceNo: $payment->reference_no
                ?: $payment->payment_no,

            partyName: $supplierName,
            description: $description,
            sourceModel: SupplierPayment::class,
            sourceId: (int) $payment->id,
            sourceType: 'Supplier Payment',
            sourceNo: $payment->payment_no,
            userId: $userId,

            lines: [
                [
                    'account_head_id' =>
                        (int) $bill->payable_account_head_id,

                    'description' =>
                        'Supplier payable settlement: '
                        . $bill->bill_no,

                    'debit' => (float) $payment->amount,
                    'credit' => 0,
                ],
                [
                    'account_head_id' =>
                        (int) $payment->payment_account_head_id,

                    'description' =>
                        'Supplier payment: '
                        . $payment->payment_no,

                    'debit' => 0,
                    'credit' => (float) $payment->amount,
                ],
            ]
        );
    }

    private function createVoucher(
        string $voucherNo,
        string $voucherDate,
        string $voucherType,
        ?string $referenceNo,
        ?string $partyName,
        string $description,
        string $sourceModel,
        int $sourceId,
        string $sourceType,
        ?string $sourceNo,
        int $userId,
        array $lines,
    ): int {
        $totalDebit = round(
            (float) collect($lines)->sum('debit'),
            2
        );

        $totalCredit = round(
            (float) collect($lines)->sum('credit'),
            2
        );

        if (
            $totalDebit <= 0
            || abs($totalDebit - $totalCredit) >= 0.01
        ) {
            throw ValidationException::withMessages([
                'voucher' =>
                    'Accounting voucher debit aur credit balanced nahi hain.',
            ]);
        }

        $timestamp = now();

        $voucherId = DB::table('account_vouchers')
            ->insertGetId([
                'voucher_no' => $voucherNo,
                'voucher_date' => $voucherDate,
                'voucher_type' => $voucherType,
                'reference_no' => $referenceNo,
                'party_name' => $partyName,
                'description' => $description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => 'Posted',
                'created_by' => $userId,
                'source_model' => $sourceModel,
                'source_id' => $sourceId,
                'source_type' => $sourceType,
                'source_no' => $sourceNo,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        foreach (array_values($lines) as $index => $line) {
            $lineId = DB::table('account_voucher_lines')
                ->insertGetId([
                    'account_voucher_id' => $voucherId,
                    'account_head_id' =>
                        (int) $line['account_head_id'],

                    'description' =>
                        $line['description'] ?? $description,

                    'debit' => round(
                        (float) ($line['debit'] ?? 0),
                        2
                    ),

                    'credit' => round(
                        (float) ($line['credit'] ?? 0),
                        2
                    ),

                    'sort_order' => $index + 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

            DB::table('account_ledgers')->insert([
                'account_head_id' =>
                    (int) $line['account_head_id'],

                'account_voucher_id' => $voucherId,
                'account_voucher_line_id' => $lineId,
                'entry_date' => $voucherDate,
                'voucher_no' => $voucherNo,
                'voucher_type' => $voucherType,
                'reference_no' => $referenceNo,

                'description' =>
                    $line['description'] ?? $description,

                'debit' => round(
                    (float) ($line['debit'] ?? 0),
                    2
                ),

                'credit' => round(
                    (float) ($line['credit'] ?? 0),
                    2
                ),

                'status' => 'Posted',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        return $voucherId;
    }

    private function findExistingVoucher(
        string $sourceModel,
        int $sourceId,
    ): ?int {
        $voucher = DB::table('account_vouchers')
            ->where('source_model', $sourceModel)
            ->where('source_id', $sourceId)
            ->first([
                'id',
                'status',
            ]);

        if (! $voucher) {
            return null;
        }

        if ($voucher->status !== 'Posted') {
            throw ValidationException::withMessages([
                'voucher' =>
                    'Is transaction ka accounting voucher cancelled ya inactive hai.',
            ]);
        }

        return (int) $voucher->id;
    }

    private function reverseVoucher(int $voucherId): void
    {
        DB::table('account_vouchers')
            ->whereKey($voucherId)
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
                $field =>
                    $label . ' active Account Head nahi hai.',
            ]);
        }

        return $accountHead;
    }
}