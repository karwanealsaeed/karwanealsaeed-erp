<?php

namespace App\Filament\Pages;

use App\Models\AccountHead;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BalanceSheetReport extends Page
{
    protected string $view = 'filament.pages.balance-sheet-report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Balance Sheet Report';

    protected static ?int $navigationSort = 33;

    protected static ?string $title = 'Balance Sheet Report';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('reports_view') ?? false);
    }

    public function assetRows(): Collection
    {
        return $this->rowsByType('Asset');
    }

    public function liabilityRows(): Collection
    {
        return $this->rowsByType('Liability');
    }

    public function equityRows(): Collection
    {
        return $this->rowsByType('Equity');
    }

    private function rowsByType(string $type): Collection
    {
        return AccountHead::query()
            ->where('type', $type)
            ->where('status', 'Active')
            ->orderBy('code')
            ->get()
            ->map(function (AccountHead $account) use ($type): array {
                $ledgerQuery = $account->ledgers()->where('status', 'Posted');

                if (request('to_date')) {
                    $ledgerQuery->whereDate('entry_date', '<=', request('to_date'));
                }

                $debit = (float) $account->opening_debit + (float) $ledgerQuery->sum('debit');
                $credit = (float) $account->opening_credit + (float) $ledgerQuery->sum('credit');

                $amount = $type === 'Asset'
                    ? ($debit - $credit)
                    : ($credit - $debit);

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn (array $row): bool => round((float) $row['amount'], 2) !== 0.0)
            ->values();
    }
}