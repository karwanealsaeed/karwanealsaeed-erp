<?php

namespace App\Console\Commands;

use App\Models\AccountHead;
use App\Models\AccountVoucher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncAccountingPostings extends Command
{
    protected $signature = 'accounting:sync-postings';

    protected $description = 'Force sync accounting vouchers from old payments and expenses tables';

    public function handle(): int
    {
        if (! Schema::hasTable('account_vouchers')) {
            $this->error('account_vouchers table not found. Run php artisan migrate first.');
            return self::FAILURE;
        }

        foreach (['source_model', 'source_id', 'source_type', 'source_no'] as $column) {
            if (! Schema::hasColumn('account_vouchers', $column)) {
                $this->error("account_vouchers.{$column} missing. Run source fields migration first.");
                return self::FAILURE;
            }
        }

        $this->ensureDefaultAccounts();

        $paymentTables = [
            'payments' => 'Visa Sales Income',
            'flight_payments' => 'Flight Sales Income',
            'umrah_payments' => 'Umrah Sales Income',
            'hotel_payments' => 'Hotel Sales Income',
        ];

        $totalCreated = 0;

        foreach ($paymentTables as $table => $incomeAccountName) {
            $totalCreated += $this->syncPaymentTable($table, $incomeAccountName);
        }

        $totalCreated += $this->syncExpensesTable();

        $this->info('');
        $this->info('Done. Total synced vouchers: ' . $totalCreated);
        $this->info('Now check: Finance → Accounting Vouchers');

        return self::SUCCESS;
    }

    private function syncPaymentTable(string $table, string $incomeAccountName): int
    {
        if (! Schema::hasTable($table)) {
            $this->warn("Skipped {$table}: table not found.");
            return 0;
        }

        $count = DB::table($table)->count();
        $this->info("Syncing {$table}: {$count} record(s)");

        if ($count <= 0) {
            return 0;
        }

        $cashAccount = $this->accountByName('Cash in Hand', 'Asset', 'Cash');
        $bankAccount = $this->accountByName('Bank Account', 'Asset', 'Bank');
        $incomeAccount = $this->accountByName($incomeAccountName, 'Income', 'Sales');

        $synced = 0;

        DB::table($table)
            ->orderBy('id')
            ->chunk(100, function ($records) use ($table, $cashAccount, $bankAccount, $incomeAccount, &$synced): void {
                foreach ($records as $record) {
                    $amount = $this->firstAmount($record, [
                        'amount',
                        'paid_amount',
                        'payment_amount',
                        'received_amount',
                        'total_amount',
                        'fee',
                        'total_fee',
                    ]);

                    if ($amount <= 0) {
                        $this->warn("Skipped {$table} ID {$record->id}: amount is 0 or amount column not found.");
                        continue;
                    }

                    $method = strtolower($this->firstText($record, [
                        'payment_method',
                        'method',
                        'paid_by',
                        'account_type',
                    ]));

                    $cashOrBank = str_contains($method, 'bank')
                        || str_contains($method, 'transfer')
                        || str_contains($method, 'online')
                        || str_contains($method, 'cheque')
                            ? $bankAccount
                            : $cashAccount;

                    $sourceNo = $this->sourceNo($table, $record);
                    $partyName = $this->partyName($record);

                    $this->createVoucher(
                        table: $table,
                        record: $record,
                        voucherType: 'Receipt',
                        sourceNo: $sourceNo,
                        partyName: $partyName,
                        description: ucfirst(str_replace('_', ' ', $table)) . ' auto receipt - ' . $sourceNo,
                        debitAccount: $cashOrBank,
                        creditAccount: $incomeAccount,
                        amount: $amount
                    );

                    $synced++;
                }
            });

        $this->info("Synced {$table}: {$synced}");
        return $synced;
    }

    private function syncExpensesTable(): int
    {
        $table = 'expenses';

        if (! Schema::hasTable($table)) {
            $this->warn('Skipped expenses: table not found.');
            return 0;
        }

        $count = DB::table($table)->count();
        $this->info("Syncing expenses: {$count} record(s)");

        if ($count <= 0) {
            return 0;
        }

        $cashAccount = $this->accountByName('Cash in Hand', 'Asset', 'Cash');
        $bankAccount = $this->accountByName('Bank Account', 'Asset', 'Bank');
        $expenseAccount = $this->accountByName('Office Expense', 'Expense', 'Office');

        $synced = 0;

        DB::table($table)
            ->orderBy('id')
            ->chunk(100, function ($records) use ($table, $cashAccount, $bankAccount, $expenseAccount, &$synced): void {
                foreach ($records as $record) {
                    $amount = $this->firstAmount($record, [
                        'amount',
                        'paid_amount',
                        'total_amount',
                        'expense_amount',
                    ]);

                    if ($amount <= 0) {
                        $this->warn("Skipped expenses ID {$record->id}: amount is 0 or amount column not found.");
                        continue;
                    }

                    $method = strtolower($this->firstText($record, [
                        'payment_method',
                        'method',
                        'paid_by',
                        'account_type',
                    ]));

                    $cashOrBank = str_contains($method, 'bank')
                        || str_contains($method, 'transfer')
                        || str_contains($method, 'online')
                        || str_contains($method, 'cheque')
                            ? $bankAccount
                            : $cashAccount;

                    $sourceNo = $this->sourceNo($table, $record);
                    $partyName = $this->partyName($record);

                    $this->createVoucher(
                        table: $table,
                        record: $record,
                        voucherType: 'Payment',
                        sourceNo: $sourceNo,
                        partyName: $partyName,
                        description: 'Expense auto payment - ' . $sourceNo,
                        debitAccount: $expenseAccount,
                        creditAccount: $cashOrBank,
                        amount: $amount
                    );

                    $synced++;
                }
            });

        $this->info("Synced expenses: {$synced}");
        return $synced;
    }

    private function createVoucher(
        string $table,
        object $record,
        string $voucherType,
        string $sourceNo,
        string $partyName,
        string $description,
        AccountHead $debitAccount,
        AccountHead $creditAccount,
        float $amount
    ): void {
        DB::transaction(function () use (
            $table,
            $record,
            $voucherType,
            $sourceNo,
            $partyName,
            $description,
            $debitAccount,
            $creditAccount,
            $amount
        ): void {
            $voucher = AccountVoucher::query()->firstOrNew([
                'source_model' => $table,
                'source_id' => $record->id,
            ]);

            $voucher->forceFill([
                'voucher_date' => $this->recordDate($record),
                'voucher_type' => $voucherType,
                'reference_no' => $sourceNo,
                'party_name' => $partyName,
                'description' => $description,
                'status' => $this->recordStatus($record),
                'source_model' => $table,
                'source_id' => $record->id,
                'source_type' => $table,
                'source_no' => $sourceNo,
            ]);

            $voucher->save();

            $voucher->lines()->delete();

            $voucher->lines()->create([
                'account_head_id' => $debitAccount->id,
                'description' => $description,
                'debit' => $amount,
                'credit' => 0,
                'sort_order' => 1,
            ]);

            $voucher->lines()->create([
                'account_head_id' => $creditAccount->id,
                'description' => $description,
                'debit' => 0,
                'credit' => $amount,
                'sort_order' => 2,
            ]);

            $voucher->refreshTotals();
            $voucher->syncLedgerEntries();
        });
    }

    private function ensureDefaultAccounts(): void
    {
        $accounts = [
            ['code' => 'A-10001', 'name' => 'Cash in Hand', 'type' => 'Asset', 'sub_type' => 'Cash'],
            ['code' => 'A-10002', 'name' => 'Bank Account', 'type' => 'Asset', 'sub_type' => 'Bank'],
            ['code' => 'I-40001', 'name' => 'Visa Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40002', 'name' => 'Flight Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40003', 'name' => 'Umrah Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40004', 'name' => 'Hotel Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'X-50001', 'name' => 'Office Expense', 'type' => 'Expense', 'sub_type' => 'Office'],
        ];

        foreach ($accounts as $account) {
            AccountHead::query()->firstOrCreate(
                ['name' => $account['name']],
                $account + [
                    'opening_debit' => 0,
                    'opening_credit' => 0,
                    'status' => 'Active',
                ]
            );
        }
    }

    private function accountByName(string $name, string $type, string $subType): AccountHead
    {
        return AccountHead::query()->firstOrCreate(
            ['name' => $name],
            [
                'code' => $this->nextAccountCode($type),
                'type' => $type,
                'sub_type' => $subType,
                'opening_debit' => 0,
                'opening_credit' => 0,
                'status' => 'Active',
            ]
        );
    }

    private function nextAccountCode(string $type): string
    {
        $prefix = match ($type) {
            'Asset' => 'A',
            'Liability' => 'L',
            'Equity' => 'E',
            'Income' => 'I',
            'Expense' => 'X',
            default => 'A',
        };

        return $prefix . '-' . str_pad((string) (((int) AccountHead::query()->max('id')) + 1), 5, '0', STR_PAD_LEFT);
    }

    private function firstAmount(object $record, array $columns): float
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && is_numeric($record->{$column})) {
                return (float) $record->{$column};
            }
        }

        return 0;
    }

    private function firstText(object $record, array $columns): string
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return $this->cleanValue($record->{$column});
            }
        }

        return '';
    }

    private function sourceNo(string $table, object $record): string
    {
        foreach ([
            'receipt_no',
            'payment_no',
            'voucher_no',
            'expense_no',
            'reference_no',
            'invoice_no',
            'booking_no',
            'application_no',
        ] as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return (string) $record->{$column};
            }
        }

        return strtoupper(substr($table, 0, 2)) . '-' . str_pad((string) $record->id, 5, '0', STR_PAD_LEFT);
    }

    private function partyName(object $record): string
    {
        foreach ([
            'customer_name',
            'supplier_name',
            'party_name',
            'name',
            'paid_to',
            'received_from',
        ] as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return $this->cleanValue($record->{$column});
            }
        }

        if (property_exists($record, 'customer_id') && $record->customer_id && Schema::hasTable('customers')) {
            return DB::table('customers')->where('id', $record->customer_id)->value('name') ?: '-';
        }

        if (property_exists($record, 'supplier_id') && $record->supplier_id && Schema::hasTable('suppliers')) {
            return DB::table('suppliers')->where('id', $record->supplier_id)->value('name') ?: '-';
        }

        return '-';
    }

    private function recordDate(object $record): string
    {
        foreach ([
            'payment_date',
            'paid_at',
            'date',
            'expense_date',
            'created_at',
        ] as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return \Carbon\Carbon::parse($record->{$column})->toDateString();
            }
        }

        return now()->toDateString();
    }

    private function recordStatus(object $record): string
    {
        $status = strtolower((string) ($record->status ?? ''));

        if (in_array($status, ['cancelled', 'canceled', 'void', 'deleted'], true)) {
            return 'Cancelled';
        }

        return 'Posted';
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
}