<?php

namespace App\Filament\Pages;

use App\Models\AccountHead;
use App\Models\AccountLedger;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CashBankBookReport extends Page
{
    protected string $view = 'filament.pages.cash-bank-book-report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Cash / Bank Book Report';

    protected static ?int $navigationSort = 34;

    protected static ?string $title = 'Cash / Bank Book Report';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('reports_view') ?? false);
    }

    public function accounts(): Collection
    {
        return AccountHead::query()
            ->where('status', 'Active')
            ->whereIn('sub_type', ['Cash', 'Bank'])
            ->orderBy('code')
            ->get();
    }

    public function selectedAccount(): ?AccountHead
    {
        if (! request('account_id')) {
            return null;
        }

        return AccountHead::find((int) request('account_id'));
    }

    public function rows(): Collection
    {
        $accountIds = $this->accounts()->pluck('id')->all();

        $query = AccountLedger::query()
            ->with('accountHead')
            ->whereIn('account_head_id', $accountIds)
            ->where('status', 'Posted')
            ->orderBy('entry_date')
            ->orderBy('id');

        if (request('account_id')) {
            $query->where('account_head_id', (int) request('account_id'));
        }

        if (request('from_date')) {
            $query->whereDate('entry_date', '>=', request('from_date'));
        }

        if (request('to_date')) {
            $query->whereDate('entry_date', '<=', request('to_date'));
        }

        $balance = 0;

        return $query->get()->map(function (AccountLedger $ledger) use (&$balance): array {
            $balance += (float) $ledger->debit - (float) $ledger->credit;

            return [
                'date' => $ledger->entry_date?->format('d M Y'),
                'account' => $ledger->accountHead?->display_name ?: '-',
                'voucher_no' => $ledger->voucher_no,
                'type' => $ledger->voucher_type,
                'description' => $ledger->description,
                'debit' => (float) $ledger->debit,
                'credit' => (float) $ledger->credit,
                'balance' => $balance,
            ];
        });
    }
}