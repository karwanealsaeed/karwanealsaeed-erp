<?php

namespace App\Services;

use App\Models\AccountHead;
use App\Models\AccountVoucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AccountingAutoPostingService
{
    public function postFromModel(Model $model): void
    {
        if (! Schema::hasTable('account_vouchers') || ! Schema::hasTable('account_heads')) {
            return;
        }

        $class = get_class($model);

        if ($this->isPayrollModel($class)) {
            $this->runPostingWithAudit(
                model: $model,
                posting: function () use ($model): bool {
                    $this->assertGeneralLedgerPeriodOpen(
                        model: $model,
                        context: 'Payroll Automatic Accounting Posting',
                    );

                    return $this->postPayroll($model);
                },
            );

            return;
        }

        if ($this->isPaymentModel($class)) {
            $this->runPostingWithAudit(
                model: $model,
                posting: function () use ($model): bool {
                    $this->assertGeneralLedgerPeriodOpen(
                        model: $model,
                        context: 'Payment Automatic Accounting Posting',
                    );

                    return $this->postPayment($model);
                },
            );

            return;
        }

        if ($this->isExpenseModel($class)) {
            $this->runPostingWithAudit(
                model: $model,
                posting: function () use ($model): bool {
                    $this->assertGeneralLedgerPeriodOpen(
                        model: $model,
                        context: 'Expense Automatic Accounting Posting',
                    );

                    return $this->postExpense($model);
                },
            );

            return;
        }
    }

    public function removeForModel(Model $model): void
    {
        if (! Schema::hasTable('account_vouchers')) {
            return;
        }

        AccountVoucher::query()
            ->where('source_model', get_class($model))
            ->where('source_id', $model->getKey())
            ->get()
            ->each(fn (AccountVoucher $voucher) => $voucher->delete());
    }

    private function runPostingWithAudit(
        Model $model,
        callable $posting,
    ): void {
        try {
            $postingCompleted = DB::transaction(
                function () use ($posting): bool {
                    return (bool) $posting();
                }
            );

            app(ActivityLogger::class)
                ->accountingPosting(
                    model: $model,
                    result: $postingCompleted
                        ? 'success'
                        : 'skipped',
                );
        } catch (Throwable $exception) {
            app(ActivityLogger::class)
                ->accountingPosting(
                    model: $model,
                    result: 'failed',
                    exception: $exception,
                );

            throw $exception;
        }
    }
    private function assertGeneralLedgerPeriodOpen(
        Model $model,
        string $context,
    ): void {
        app(
            AccountingPeriodLockService::class
        )->assertDateIsOpen(
            date: $this->date($model),
            scope:
                AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
            context: $context,
        );
    }
    private function isPaymentModel(string $class): bool
    {
        return in_array(
            class_basename(
                $class,
            ),
            [
                'Payment',
                'VisaPayment',
                'FlightPayment',
                'UmrahPayment',
                'HotelPayment',
                'TravelInsurancePayment',
            ],
            true,
        );
    }

    private function isExpenseModel(string $class): bool
    {
        return class_basename($class) === 'Expense';
    }

    private function isPayrollModel(string $class): bool
    {
        return class_basename($class) === 'StaffPayroll';
    }

    private function postPayment(Model $model): bool
    {
        if (
            class_basename(
                $model,
            ) === 'TravelInsurancePayment'
        ) {
            if (
                (string) $model->status
                !== 'Approved'
            ) {
                $this->removeForModel(
                    $model,
                );

                return false;
            }
        }

        $amount = $this->amount($model, [
            'amount',
            'paid_amount',
            'payment_amount',
            'received_amount',
        ]);

        if ($amount <= 0) {
            $this->removeForModel($model);
            return false;
        }

        $cashOrBank = $this->cashOrBankAccount($model);
        $income = $this->incomeAccountForPayment($model);

        if (! $cashOrBank || ! $income) {
            return false;
        }

        $sourceName = class_basename($model);
        $sourceNo = $this->sourceNo($model);
        $partyName = $this->partyName($model);

        DB::transaction(function () use ($model, $amount, $cashOrBank, $income, $sourceName, $sourceNo, $partyName): void {
            $voucher = $this->voucherForModel($model);

            $voucher->fill([
                'voucher_date' => $this->date($model),
                'voucher_type' => 'Receipt',
                'reference_no' => $sourceNo,
                'party_name' => $partyName,
                'description' => $sourceName . ' auto posting - ' . $sourceNo,
                'status' => $this->isCancelled($model) ? 'Cancelled' : 'Posted',
                'source_model' => get_class($model),
                'source_id' => $model->getKey(),
                'source_type' => $sourceName,
                'source_no' => $sourceNo,
            ]);

            $voucher->save();
            $voucher->lines()->delete();

            $voucher->lines()->create([
                'account_head_id' => $cashOrBank->id,
                'description' => 'Amount received - ' . $sourceNo,
                'debit' => $amount,
                'credit' => 0,
                'sort_order' => 1,
            ]);

            $voucher->lines()->create([
                'account_head_id' => $income->id,
                'description' => 'Service income - ' . $sourceNo,
                'debit' => 0,
                'credit' => $amount,
                'sort_order' => 2,
            ]);

            $voucher->refreshTotals();
            $voucher->syncLedgerEntries();
        });

        return true;
    }

    private function postExpense(Model $model): bool
    {
        $amount = $this->amount($model, [
            'amount',
            'paid_amount',
            'total_amount',
            'expense_amount',
        ]);

        if ($amount <= 0) {
            $this->removeForModel($model);
            return false;
        }

        $expense = $this->expenseAccount($model);
        $cashOrBank = $this->cashOrBankAccount($model);

        if (! $expense || ! $cashOrBank) {
            return false;
        }

        $sourceName = class_basename($model);
        $sourceNo = $this->sourceNo($model);
        $partyName = $this->partyName($model);

        DB::transaction(function () use ($model, $amount, $expense, $cashOrBank, $sourceName, $sourceNo, $partyName): void {
            $voucher = $this->voucherForModel($model);

            $voucher->fill([
                'voucher_date' => $this->date($model),
                'voucher_type' => 'Payment',
                'reference_no' => $sourceNo,
                'party_name' => $partyName,
                'description' => $sourceName . ' auto posting - ' . $sourceNo,
                'status' => $this->isCancelled($model) ? 'Cancelled' : 'Posted',
                'source_model' => get_class($model),
                'source_id' => $model->getKey(),
                'source_type' => $sourceName,
                'source_no' => $sourceNo,
            ]);

            $voucher->save();
            $voucher->lines()->delete();

            $voucher->lines()->create([
                'account_head_id' => $expense->id,
                'description' => 'Expense posted - ' . $sourceNo,
                'debit' => $amount,
                'credit' => 0,
                'sort_order' => 1,
            ]);

            $voucher->lines()->create([
                'account_head_id' => $cashOrBank->id,
                'description' => 'Expense paid - ' . $sourceNo,
                'debit' => 0,
                'credit' => $amount,
                'sort_order' => 2,
            ]);

            $voucher->refreshTotals();
            $voucher->syncLedgerEntries();
        });

        return true;
    }

    private function postPayroll(Model $model): bool
    {
        $amount = (float) ($model->paid_amount ?? 0);

        if ($amount <= 0) {
            $this->removeForModel($model);
            return false;
        }

        $salaryExpense = AccountHead::query()
            ->where('name', 'Salary Expense')
            ->first()
            ?: AccountHead::query()->firstOrCreate(
                ['name' => 'Salary Expense'],
                [
                    'code' => 'X-' . str_pad((string) (((int) AccountHead::query()->max('id')) + 1), 5, '0', STR_PAD_LEFT),
                    'type' => 'Expense',
                    'sub_type' => 'Salary',
                    'opening_debit' => 0,
                    'opening_credit' => 0,
                    'status' => 'Active',
                ]
            );

        $cashOrBank = $this->cashOrBankAccount($model);

        if (! $salaryExpense || ! $cashOrBank) {
            return false;
        }

        $sourceName = class_basename($model);
        $sourceNo = $this->sourceNo($model);
        $partyName = method_exists($model, 'staffMember') && $model->staffMember
            ? $model->staffMember->name
            : $this->partyName($model);

        DB::transaction(function () use ($model, $amount, $salaryExpense, $cashOrBank, $sourceName, $sourceNo, $partyName): void {
            $voucher = $this->voucherForModel($model);

            $voucher->fill([
                'voucher_date' => $this->date($model),
                'voucher_type' => 'Payment',
                'reference_no' => $sourceNo,
                'party_name' => $partyName,
                'description' => $sourceName . ' auto salary posting - ' . $sourceNo,
                'status' => $this->isCancelled($model) ? 'Cancelled' : 'Posted',
                'source_model' => get_class($model),
                'source_id' => $model->getKey(),
                'source_type' => $sourceName,
                'source_no' => $sourceNo,
            ]);

            $voucher->save();
            $voucher->lines()->delete();

            $voucher->lines()->create([
                'account_head_id' => $salaryExpense->id,
                'description' => 'Salary expense - ' . $sourceNo,
                'debit' => $amount,
                'credit' => 0,
                'sort_order' => 1,
            ]);

            $voucher->lines()->create([
                'account_head_id' => $cashOrBank->id,
                'description' => 'Salary paid - ' . $sourceNo,
                'debit' => 0,
                'credit' => $amount,
                'sort_order' => 2,
            ]);

            $voucher->refreshTotals();
            $voucher->syncLedgerEntries();
        });

        return true;
    }

    private function voucherForModel(Model $model): AccountVoucher
    {
        return AccountVoucher::query()->firstOrNew([
            'source_model' => get_class($model),
            'source_id' => $model->getKey(),
        ]);
    }

    private function incomeAccountForPayment(
        Model $model,
    ): ?AccountHead {
        $modelName =
            class_basename(
                $model,
            );

        $accountName =
            match ($modelName) {
                'FlightPayment' =>
                    'Flight Sales Income',

                'UmrahPayment' =>
                    'Umrah Sales Income',

                'HotelPayment' =>
                    'Hotel Sales Income',

                'TravelInsurancePayment' =>
                    'Travel Insurance Sales Income',

                default =>
                    'Visa Sales Income',
            };

        $account =
            AccountHead::query()
                ->where(
                    'name',
                    $accountName,
                )
                ->first();

        if ($account) {
            return $account;
        }

        if (
            $modelName ===
            'TravelInsurancePayment'
        ) {
            return AccountHead::query()
                ->create(
                    [
                        'code' =>
                            'I-'
                            .str_pad(
                                (string) (
                                    ((int) AccountHead::query()
                                        ->max(
                                            'id',
                                        ))
                                    + 1
                                ),
                                5,
                                '0',
                                STR_PAD_LEFT,
                            ),

                        'name' =>
                            'Travel Insurance Sales Income',

                        'type' =>
                            'Income',

                        'sub_type' =>
                            'Sales',

                        'opening_debit' =>
                            0,

                        'opening_credit' =>
                            0,

                        'status' =>
                            'Active',
                    ],
                );
        }

        return AccountHead::query()
            ->where(
                'type',
                'Income',
            )
            ->orderBy(
                'id',
            )
            ->first();
    }

    private function expenseAccount(Model $model): ?AccountHead
    {
        return AccountHead::query()->where('name', 'Office Expense')->first()
            ?: AccountHead::query()->where('type', 'Expense')->orderBy('id')->first();
    }

    private function cashOrBankAccount(Model $model): ?AccountHead
    {
        $method = strtolower((string) $this->value($model, [
            'payment_method',
            'method',
            'paid_by',
            'account_type',
        ]));

        if (Str::contains($method, ['bank', 'transfer', 'online', 'cheque', 'check'])) {
            return AccountHead::query()->where('name', 'Bank Account')->first()
                ?: AccountHead::query()->where('sub_type', 'Bank')->first();
        }

        return AccountHead::query()->where('name', 'Cash in Hand')->first()
            ?: AccountHead::query()->where('sub_type', 'Cash')->first();
    }

    private function amount(Model $model, array $columns): float
    {
        foreach ($columns as $column) {
            if (isset($model->{$column}) && is_numeric($model->{$column})) {
                return (float) $model->{$column};
            }
        }

        return 0;
    }

    private function date(Model $model): string
    {
        foreach ([
            'payment_date',
            'paid_at',
            'date',
            'expense_date',
            'created_at',
        ] as $column) {
            if (isset($model->{$column}) && filled($model->{$column})) {
                return \Carbon\Carbon::parse($model->{$column})->toDateString();
            }
        }

        return now()->toDateString();
    }

    private function sourceNo(Model $model): string
    {
        foreach ([
            'payroll_no',
            'receipt_no',
            'payment_no',
            'voucher_no',
            'expense_no',
            'reference_no',
            'invoice_no',
            'booking_no',
            'application_no',
        ] as $column) {
            if (isset($model->{$column}) && filled($model->{$column})) {
                return (string) $model->{$column};
            }
        }

        return class_basename($model) . '-' . $model->getKey();
    }

    private function partyName(Model $model): string
    {
        if (
            class_basename(
                $model,
            ) === 'TravelInsurancePayment'
        ) {
            $customerName =
                $model->policy
                    ?->customer
                    ?->name;

            if (filled($customerName)) {
                return $this->cleanValue(
                    $customerName,
                );
            }
        }
        if (method_exists($model, 'staffMember') && $model->staffMember) {
            return (string) $model->staffMember->name;
        }

        foreach ([
            'customer_name',
            'supplier_name',
            'party_name',
            'name',
            'paid_to',
            'received_from',
        ] as $column) {
            if (isset($model->{$column}) && filled($model->{$column})) {
                return $this->cleanValue($model->{$column});
            }
        }

        if (isset($model->customer_id) && Schema::hasTable('customers')) {
            $name = DB::table('customers')->where('id', $model->customer_id)->value('name');
            if ($name) return (string) $name;
        }

        if (isset($model->supplier_id) && Schema::hasTable('suppliers')) {
            $name = DB::table('suppliers')->where('id', $model->supplier_id)->value('name');
            if ($name) return (string) $name;
        }

        return '-';
    }

    private function value(Model $model, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (isset($model->{$column}) && filled($model->{$column})) {
                return $this->cleanValue($model->{$column});
            }
        }

        return null;
    }

    private function cleanValue(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach (['name', 'title', 'code'] as $key) {
                    if (isset($decoded[$key]) && filled($decoded[$key])) {
                        return (string) $decoded[$key];
                    }
                }
            }

            return trim($value);
        }

        return (string) $value;
    }

    private function isCancelled(Model $model): bool
    {
        $status = strtolower((string) ($model->status ?? ''));

        return in_array($status, [
            'cancelled',
            'canceled',
            'void',
            'refunded',
            'deleted',
        ], true);
    }
}