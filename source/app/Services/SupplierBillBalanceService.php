<?php

namespace App\Services;

use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierBillBalanceService
{
    public function sync(
        SupplierBill|int $bill
    ): SupplierBill {
        $billId = $bill instanceof SupplierBill
            ? (int) $bill->getKey()
            : (int) $bill;

        return DB::transaction(function () use (
            $billId
        ): SupplierBill {
            $lockedBill = SupplierBill::query()
                ->lockForUpdate()
                ->findOrFail($billId);

            $paidAmount = round(
                (float) SupplierPayment::query()
                    ->where(
                        'supplier_bill_id',
                        $lockedBill->id
                    )
                    ->where('status', 'Approved')
                    ->sum('amount'),
                2
            );

            $totalAmount = round(
                (float) $lockedBill->total_amount,
                2
            );

            if ($paidAmount > $totalAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'Approved supplier payments bill total se zyada nahi ho sakti.',
                ]);
            }

            $balanceAmount = round(
                max(0, $totalAmount - $paidAmount),
                2
            );

            $status = $this->resolveStatus(
                currentStatus: $lockedBill->status,
                totalAmount: $totalAmount,
                paidAmount: $paidAmount,
                balanceAmount: $balanceAmount,
            );

            $lockedBill->updateQuietly([
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'status' => $status,
            ]);

            return $lockedBill->refresh();
        });
    }

    private function resolveStatus(
        string $currentStatus,
        float $totalAmount,
        float $paidAmount,
        float $balanceAmount,
    ): string {
        if ($currentStatus === 'Cancelled') {
            return 'Cancelled';
        }

        if (
            $currentStatus === 'Draft'
            && $paidAmount <= 0
        ) {
            return 'Draft';
        }

        if (
            $totalAmount > 0
            && $balanceAmount <= 0
        ) {
            return 'Paid';
        }

        if ($paidAmount > 0) {
            return 'Partially Paid';
        }

        return 'Unpaid';
    }
}