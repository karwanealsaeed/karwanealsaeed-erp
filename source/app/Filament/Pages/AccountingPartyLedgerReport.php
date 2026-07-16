<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountingPartyLedgerReport extends Page
{
    protected string $view = 'filament.pages.accounting-party-ledger-report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Accounting Party Ledger';

    protected static ?int $navigationSort = 35;

    protected static ?string $title = 'Accounting Party Ledger';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('reports_view') ?? false);
    }

    public function customers(): Collection
    {
        if (! Schema::hasTable('customers')) {
            return collect();
        }

        return DB::table('customers')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile']);
    }

    public function suppliers(): Collection
    {
        if (! Schema::hasTable('suppliers')) {
            return collect();
        }

        $nameColumn = Schema::hasColumn('suppliers', 'name') ? 'name' : 'supplier_name';

        return DB::table('suppliers')
            ->orderBy($nameColumn)
            ->get()
            ->map(fn ($supplier): object => (object) [
                'id' => $supplier->id,
                'name' => $supplier->{$nameColumn} ?? '-',
                'mobile' => $supplier->phone ?? $supplier->mobile ?? '',
            ]);
    }

    public function selectedPartyName(): string
    {
        $partyType = request('party_type', 'Customer');
        $partyId = (int) request('party_id');

        if (! $partyId) {
            return 'All Parties';
        }

        if ($partyType === 'Supplier') {
            return $this->suppliers()->firstWhere('id', $partyId)?->name ?: '-';
        }

        return $this->customers()->firstWhere('id', $partyId)?->name ?: '-';
    }

    public function rows(): Collection
    {
        $partyType = request('party_type', 'Customer');

        return $partyType === 'Supplier'
            ? $this->supplierRows()
            : $this->customerRows();
    }

    private function customerRows(): Collection
    {
        $rows = collect();
        $customerId = (int) request('party_id');

        $hasInvoiceRows = false;

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'customer_id')) {
            $query = DB::table('invoices');

            if ($customerId) {
                $query->where('customer_id', $customerId);
            }

            $this->applyDateFilter($query, 'invoices', ['invoice_date', 'date', 'created_at']);

            $query->orderBy($this->dateColumn('invoices', ['invoice_date', 'date', 'created_at']));

            foreach ($query->get() as $record) {
                $amount = $this->firstAmount($record, [
                    'total_amount',
                    'grand_total',
                    'net_amount',
                    'amount',
                ]);

                if ($amount <= 0) {
                    continue;
                }

                $hasInvoiceRows = true;

                $rows->push([
                    'date' => $this->recordDate($record, ['invoice_date', 'date', 'created_at']),
                    'type' => 'Invoice',
                    'ref' => $this->firstText($record, ['invoice_no', 'reference_no'], 'INV-' . $record->id),
                    'party' => $this->customerName($record->customer_id ?? null),
                    'description' => $this->firstText($record, ['description', 'service_type', 'notes'], 'Invoice posted'),
                    'debit' => $amount,
                    'credit' => 0,
                ]);
            }
        }

        if (! $hasInvoiceRows) {
            $this->addCustomerBookingRows($rows, $customerId);
        }

        foreach ([
            'payments' => 'Visa Payment',
            'flight_payments' => 'Flight Payment',
            'umrah_payments' => 'Umrah Payment',
            'hotel_payments' => 'Hotel Payment',
        ] as $table => $label) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'customer_id')) {
                continue;
            }

            $query = DB::table($table);

            if ($customerId) {
                $query->where('customer_id', $customerId);
            }

            $this->applyDateFilter($query, $table, ['payment_date', 'paid_at', 'date', 'created_at']);

            foreach ($query->orderBy($this->dateColumn($table, ['payment_date', 'paid_at', 'date', 'created_at']))->get() as $record) {
                $amount = $this->firstAmount($record, [
                    'amount',
                    'paid_amount',
                    'payment_amount',
                    'received_amount',
                    'total_amount',
                ]);

                if ($amount <= 0) {
                    continue;
                }

                $rows->push([
                    'date' => $this->recordDate($record, ['payment_date', 'paid_at', 'date', 'created_at']),
                    'type' => $label,
                    'ref' => $this->firstText($record, ['receipt_no', 'payment_no', 'reference_no'], strtoupper(substr($table, 0, 2)) . '-' . $record->id),
                    'party' => $this->customerName($record->customer_id ?? null),
                    'description' => 'Payment received',
                    'debit' => 0,
                    'credit' => $amount,
                ]);
            }
        }

        return $this->withRunningBalance($rows);
    }

    private function addCustomerBookingRows(Collection $rows, int $customerId): void
    {
        foreach ([
            'visa_applications' => ['Visa Application', ['total_fee', 'fee', 'amount']],
            'flight_bookings' => ['Flight Booking', ['total_amount', 'amount']],
            'umrah_bookings' => ['Umrah Booking', ['package_amount', 'total_amount', 'amount']],
            'hotel_bookings' => ['Hotel Booking', ['total_amount', 'amount']],
        ] as $table => [$label, $amountColumns]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'customer_id')) {
                continue;
            }

            $query = DB::table($table);

            if ($customerId) {
                $query->where('customer_id', $customerId);
            }

            $this->applyDateFilter($query, $table, ['booking_date', 'application_date', 'date', 'created_at']);

            foreach ($query->orderBy($this->dateColumn($table, ['booking_date', 'application_date', 'date', 'created_at']))->get() as $record) {
                $amount = $this->firstAmount($record, $amountColumns);

                if ($amount <= 0) {
                    continue;
                }

                $rows->push([
                    'date' => $this->recordDate($record, ['booking_date', 'application_date', 'date', 'created_at']),
                    'type' => $label,
                    'ref' => $this->firstText($record, ['booking_no', 'application_no', 'reference_no'], strtoupper(substr($table, 0, 2)) . '-' . $record->id),
                    'party' => $this->customerName($record->customer_id ?? null),
                    'description' => $label . ' posted',
                    'debit' => $amount,
                    'credit' => 0,
                ]);

                $paid = $this->firstAmount($record, ['paid_amount']);

                if ($paid > 0) {
                    $rows->push([
                        'date' => $this->recordDate($record, ['booking_date', 'application_date', 'date', 'created_at']),
                        'type' => $label . ' Paid',
                        'ref' => $this->firstText($record, ['booking_no', 'application_no', 'reference_no'], strtoupper(substr($table, 0, 2)) . '-' . $record->id),
                        'party' => $this->customerName($record->customer_id ?? null),
                        'description' => 'Paid amount recorded in booking',
                        'debit' => 0,
                        'credit' => $paid,
                    ]);
                }
            }
        }
    }

    private function supplierRows(): Collection
    {
        $rows = collect();
        $supplierId = (int) request('party_id');

        if (! Schema::hasTable('expenses')) {
            return collect();
        }

        $query = DB::table('expenses');

        if ($supplierId && Schema::hasColumn('expenses', 'supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $this->applyDateFilter($query, 'expenses', ['expense_date', 'date', 'created_at']);

        foreach ($query->orderBy($this->dateColumn('expenses', ['expense_date', 'date', 'created_at']))->get() as $record) {
            $amount = $this->firstAmount($record, [
                'total_amount',
                'expense_amount',
                'amount',
            ]);

            if ($amount <= 0) {
                continue;
            }

            $supplierName = $this->supplierName($record->supplier_id ?? null, $record);

            $rows->push([
                'date' => $this->recordDate($record, ['expense_date', 'date', 'created_at']),
                'type' => 'Expense Bill',
                'ref' => $this->firstText($record, ['expense_no', 'voucher_no', 'reference_no'], 'EXP-' . $record->id),
                'party' => $supplierName,
                'description' => $this->firstText($record, ['title', 'description', 'notes'], 'Expense posted'),
                'debit' => 0,
                'credit' => $amount,
            ]);

            $paid = $this->firstAmount($record, [
                'paid_amount',
                'amount_paid',
                'payment_amount',
            ]);

            if ($paid > 0) {
                $rows->push([
                    'date' => $this->recordDate($record, ['expense_date', 'date', 'created_at']),
                    'type' => 'Supplier Payment',
                    'ref' => $this->firstText($record, ['expense_no', 'voucher_no', 'reference_no'], 'EXP-' . $record->id),
                    'party' => $supplierName,
                    'description' => 'Payment to supplier',
                    'debit' => $paid,
                    'credit' => 0,
                ]);
            }
        }

        return $this->withRunningBalance($rows);
    }

    private function withRunningBalance(Collection $rows): Collection
    {
        $balance = 0;

        return $rows
            ->sortBy(fn (array $row): string => ($row['date'] ?: '1900-01-01') . $row['ref'])
            ->values()
            ->map(function (array $row) use (&$balance): array {
                $balance += (float) $row['debit'] - (float) $row['credit'];

                $row['balance'] = $balance;
                $row['balance_text'] = $this->balanceText($balance);

                return $row;
            });
    }

    private function balanceText(float $balance): string
    {
        if ($balance > 0) {
            return 'PKR ' . number_format(abs($balance), 2) . ' Dr';
        }

        if ($balance < 0) {
            return 'PKR ' . number_format(abs($balance), 2) . ' Cr';
        }

        return 'PKR 0.00';
    }

    private function applyDateFilter($query, string $table, array $dateColumns): void
    {
        $dateColumn = $this->dateColumn($table, $dateColumns);

        if (request('from_date')) {
            $query->whereDate($dateColumn, '>=', request('from_date'));
        }

        if (request('to_date')) {
            $query->whereDate($dateColumn, '<=', request('to_date'));
        }
    }

    private function dateColumn(string $table, array $dateColumns): string
    {
        foreach ($dateColumns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return 'created_at';
    }

    private function recordDate(object $record, array $columns): string
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return Carbon::parse($record->{$column})->format('Y-m-d');
            }
        }

        return now()->format('Y-m-d');
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

    private function firstText(object $record, array $columns, string $fallback = '-'): string
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return $this->cleanValue($record->{$column});
            }
        }

        return $fallback;
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

    private function customerName(mixed $customerId): string
    {
        if (! $customerId || ! Schema::hasTable('customers')) {
            return '-';
        }

        return DB::table('customers')->where('id', $customerId)->value('name') ?: '-';
    }

    private function supplierName(mixed $supplierId, ?object $record = null): string
    {
        if ($supplierId && Schema::hasTable('suppliers')) {
            $nameColumn = Schema::hasColumn('suppliers', 'name') ? 'name' : 'supplier_name';

            $name = DB::table('suppliers')->where('id', $supplierId)->value($nameColumn);

            if ($name) {
                return (string) $name;
            }
        }

        if ($record) {
            return $this->firstText($record, ['supplier_name', 'paid_to', 'party_name'], '-');
        }

        return '-';
    }
}