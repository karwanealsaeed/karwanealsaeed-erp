<?php

namespace App\Filament\Pages;

use App\Models\AccountHead;
use App\Models\AccountLedger;
use App\Support\RbacPermissionRegistry;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AccountLedgerReport extends Page
{
    protected string $view = 'filament.pages.account-ledger-report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'General Ledger';

    protected static ?int $navigationSort = 31;

    protected static ?string $title = 'General Ledger';

    public static function canAccess(): bool
    {
        return RbacPermissionRegistry::allowsPage(
            auth()->user(),
            static::class,
        );
    }

    public function accounts(): Collection
    {
        return AccountHead::query()->where('status', 'Active')->orderBy('code')->get();
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
        if (! request('account_id')) {
            return collect();
        }

        $query = AccountLedger::query()
            ->where('account_head_id', (int) request('account_id'))
            ->where('status', 'Posted')
            ->orderBy('entry_date')
            ->orderBy('id');

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