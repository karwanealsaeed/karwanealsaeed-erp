<?php

namespace App\Filament\Pages;

use App\Models\AccountHead;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class TrialBalanceReport extends Page
{
    protected string $view = 'filament.pages.trial-balance-report';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-scale';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Trial Balance Report';

    protected static ?int $navigationSort = 30;

    protected static ?string $title = 'Trial Balance Report';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('reports_view') ?? false);
    }

    public function rows(): Collection
    {
        return AccountHead::query()
            ->where('status', 'Active')
            ->orderBy('code')
            ->get()
            ->map(function (AccountHead $account): array {
                $debit = (float) $account->opening_debit + (float) $account->ledgers()->where('status', 'Posted')->sum('debit');
                $credit = (float) $account->opening_credit + (float) $account->ledgers()->where('status', 'Posted')->sum('credit');
                $balance = $debit - $credit;

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ];
            });
    }
}