<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Filament\Pages\Page;

class ExpenseCategoryReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 14;
    protected static ?string $navigationLabel = 'Expense Category Report';
    protected static ?string $title = 'Expense Category Report';

    protected string $view = 'filament.pages.expense-category-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $fromDate = Carbon::parse($this->fromDate ?? now()->startOfMonth())->startOfDay();
        $toDate = Carbon::parse($this->toDate ?? now())->endOfDay();

        $rows = [];

        $categories = ExpenseCategory::orderBy('name')->get();

        foreach ($categories as $category) {
            $totalAmount = Expense::where('expense_category_id', $category->id)
                ->whereBetween('expense_date', [$fromDate, $toDate])
                ->sum('amount');

            $expenseCount = Expense::where('expense_category_id', $category->id)
                ->whereBetween('expense_date', [$fromDate, $toDate])
                ->count();

            if ($totalAmount <= 0) {
                continue;
            }

            $rows[] = [
                'category_name' => $category->name,
                'category_code' => $category->code,
                'expense_count' => $expenseCount,
                'total_amount' => $totalAmount,
            ];
        }

        return [
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'rows' => $rows,
            'total_expenses' => collect($rows)->sum('total_amount'),
            'total_records' => collect($rows)->sum('expense_count'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


