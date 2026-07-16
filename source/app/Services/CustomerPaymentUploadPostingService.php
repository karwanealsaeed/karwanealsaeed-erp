<?php

namespace App\Services;

use App\Models\AccountHead;
use App\Models\AccountVoucher;
use App\Models\CustomerPaymentUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerPaymentUploadPostingService
{
    public function sync(CustomerPaymentUpload $upload): void
    {
        if (! $this->accountingReady()) {
            return;
        }

        if ($upload->status !== 'Approved' || (float) $upload->amount <= 0) {
            $this->remove($upload);
            return;
        }

        if (blank($upload->approved_at) || blank($upload->approved_by)) {
            $upload->updateQuietly([
                'approved_at' => $upload->approved_at ?: now(),
                'approved_by' => $upload->approved_by ?: auth()->id(),
            ]);
        }

        $cashOrBank = $this->cashOrBankAccount($upload);
        $income = $this->incomeAccount($upload);

        if (! $cashOrBank || ! $income) {
            return;
        }

        DB::transaction(function () use ($upload, $cashOrBank, $income): void {
            $voucher = AccountVoucher::query()->firstOrNew([
                'source_model' => CustomerPaymentUpload::class,
                'source_id' => $upload->id,
            ]);

            $voucher->fill([
                'voucher_date' => $upload->payment_date ?: now()->toDateString(),
                'voucher_type' => 'Receipt',
                'reference_no' => $upload->upload_no,
                'party_name' => $upload->customer?->name ?: '-',
                'description' => 'Customer payment upload approved - ' . $upload->upload_no,
                'status' => 'Posted',
                'source_model' => CustomerPaymentUpload::class,
                'source_id' => $upload->id,
                'source_type' => 'CustomerPaymentUpload',
                'source_no' => $upload->upload_no,
            ]);

            $voucher->save();

            $voucher->lines()->delete();

            $voucher->lines()->create([
                'account_head_id' => $cashOrBank->id,
                'description' => 'Payment received from customer - ' . $upload->upload_no,
                'debit' => (float) $upload->amount,
                'credit' => 0,
                'sort_order' => 1,
            ]);

            $voucher->lines()->create([
                'account_head_id' => $income->id,
                'description' => ($upload->service_type ?: 'Service') . ' income - ' . $upload->upload_no,
                'debit' => 0,
                'credit' => (float) $upload->amount,
                'sort_order' => 2,
            ]);

            $voucher->refreshTotals();
            $voucher->syncLedgerEntries();

            $upload->updateQuietly([
                'account_voucher_id' => $voucher->id,
            ]);
        });
    }

    public function remove(CustomerPaymentUpload $upload): void
    {
        if (! $this->accountingReady()) {
            return;
        }

        AccountVoucher::query()
            ->where('source_model', CustomerPaymentUpload::class)
            ->where('source_id', $upload->id)
            ->get()
            ->each(fn (AccountVoucher $voucher) => $voucher->delete());

        if ($upload->exists && $upload->account_voucher_id) {
            $upload->updateQuietly([
                'account_voucher_id' => null,
                'approved_at' => $upload->status === 'Approved' ? $upload->approved_at : null,
                'approved_by' => $upload->status === 'Approved' ? $upload->approved_by : null,
            ]);
        }
    }

    private function accountingReady(): bool
    {
        return class_exists(AccountVoucher::class)
            && class_exists(AccountHead::class)
            && Schema::hasTable('account_heads')
            && Schema::hasTable('account_vouchers')
            && Schema::hasTable('account_voucher_lines')
            && Schema::hasTable('account_ledgers');
    }

    private function cashOrBankAccount(CustomerPaymentUpload $upload): ?AccountHead
    {
        $method = strtolower((string) $upload->payment_method);

        if (Str::contains($method, ['bank', 'transfer', 'online', 'cheque', 'check'])) {
            return $this->account('Bank Account', 'Asset', 'Bank');
        }

        return $this->account('Cash in Hand', 'Asset', 'Cash');
    }

    private function incomeAccount(CustomerPaymentUpload $upload): ?AccountHead
    {
        $service = strtolower((string) $upload->service_type);

        $name = match (true) {
            Str::contains($service, 'flight') => 'Flight Sales Income',
            Str::contains($service, 'umrah') => 'Umrah Sales Income',
            Str::contains($service, 'hotel') => 'Hotel Sales Income',
            Str::contains($service, 'visa') => 'Visa Sales Income',
            Str::contains($service, 'insurance') => 'Service Income',
            Str::contains($service, 'tour') => 'Service Income',
            default => 'Service Income',
        };

        return $this->account($name, 'Income', 'Service');
    }

    private function account(string $name, string $type, string $subType): ?AccountHead
    {
        $account = AccountHead::query()
            ->where('name', $name)
            ->first();

        if ($account) {
            return $account;
        }

        $prefix = match ($type) {
            'Asset' => 'A',
            'Liability' => 'L',
            'Equity' => 'E',
            'Income' => 'I',
            'Expense' => 'X',
            default => 'A',
        };

        return AccountHead::query()->create([
            'code' => $prefix . '-' . str_pad((string) (((int) AccountHead::query()->max('id')) + 1), 5, '0', STR_PAD_LEFT),
            'name' => $name,
            'type' => $type,
            'sub_type' => $subType,
            'opening_debit' => 0,
            'opening_credit' => 0,
            'status' => 'Active',
        ]);
    }
}