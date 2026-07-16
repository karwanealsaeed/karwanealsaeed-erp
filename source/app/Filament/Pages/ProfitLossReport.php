<?php

namespace App\Filament\Pages;

use App\Models\AccountHead;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ProfitLossReport extends Page
{
    protected string $view = 'filament.pages.profit-loss-report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Profit & Loss Report';

    protected static ?int $navigationSort = 32;

    protected static ?string $title = 'Profit & Loss Report';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('reports_view') ?? false);
    }

    public function incomeRows(): Collection
    {
        return $this->rowsByType('Income');
    }

    public function expenseRows(): Collection
    {
        return $this->rowsByType('Expense');
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

                if (request('from_date')) {
                    $ledgerQuery->whereDate('entry_date', '>=', request('from_date'));
                }

                if (request('to_date')) {
                    $ledgerQuery->whereDate('entry_date', '<=', request('to_date'));
                }

                $debit = (float) $ledgerQuery->sum('debit');
                $credit = (float) $ledgerQuery->sum('credit');

                $amount = $type === 'Income'
                    ? ($credit - $debit)
                    : ($debit - $credit);

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