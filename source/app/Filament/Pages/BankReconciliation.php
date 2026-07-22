<?php

namespace App\Filament\Pages;

use App\Models\BankAccount;
use App\Services\BankReconciliationService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class BankReconciliation extends Page
{

    use \App\Filament\Concerns\HasRbacPageAuthorization;
protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-scale';

    protected static string|UnitEnum|null
        $navigationGroup = 'Finance';

    protected static ?string $navigationLabel =
        'Bank Reconciliation';

    protected static ?string $title =
        'Bank Reconciliation';

    protected static ?string $slug =
        'bank-reconciliation';

    protected static ?int $navigationSort = 42;

    protected string $view =
        'filament.pages.bank-reconciliation';

    public int|string|null $bankAccountId = null;

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()
            ->startOfMonth()
            ->format('Y-m-d');

        $this->dateTo = now()
            ->endOfMonth()
            ->format('Y-m-d');

        $this->bankAccountId =
            BankAccount::query()
                ->where('status', 'Active')
                ->orderBy('bank_name')
                ->value('id');
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()
            ->startOfMonth()
            ->format('Y-m-d');

        $this->dateTo = now()
            ->endOfMonth()
            ->format('Y-m-d');

        $this->bankAccountId =
            BankAccount::query()
                ->where('status', 'Active')
                ->orderBy('bank_name')
                ->value('id');
    }

    public function bankAccountOptions(): array
    {
        return BankAccount::query()
            ->where('status', 'Active')
            ->orderBy('bank_name')
            ->get()
            ->mapWithKeys(
                fn (
                    BankAccount $account
                ): array => [
                    $account->id =>
                        $account->display_name,
                ]
            )
            ->toArray();
    }

    public function reportData(): array
    {
        return app(
            BankReconciliationService::class
        )->generate(
            bankAccountId: $this->bankAccountId,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}