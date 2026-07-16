<?php

namespace App\Models;

use App\Services\AccountingPeriodLockService;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_no',
        'expense_category_id',
        'expense_date',
        'amount',
        'payment_method',
        'reference_no',
        'description',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (Expense $expense): void {
                $expense->assertExpensePeriodOpen(
                    context: 'Expense Creation',
                    date: $expense->expense_date,
                );
            }
        );

        static::updating(
            function (Expense $expense): void {
                $originalDate =
                    $expense->getRawOriginal(
                        'expense_date'
                    );

                if (filled($originalDate)) {
                    $expense->assertExpensePeriodOpen(
                        context:
                            'Expense Editing - Original Period',

                        date: $originalDate,
                    );
                }

                $expense->assertExpensePeriodOpen(
                    context:
                        'Expense Editing - New Period',

                    date: $expense->expense_date,
                );
            }
        );

        static::deleting(
            function (Expense $expense): void {
                $originalDate =
                    $expense->getRawOriginal(
                        'expense_date'
                    );

                $expense->assertExpensePeriodOpen(
                    context:
                        'Expense Deletion',

                    date:
                        $originalDate
                        ?: $expense->expense_date,
                );
            }
        );
    }

    public function category()
    {
        return $this->belongsTo(
            ExpenseCategory::class,
            'expense_category_id'
        );
    }

    public function isPeriodLocked(): bool
    {
        return app(
            AccountingPeriodLockService::class
        )->isDateLocked(
            date: $this->expense_date,
            scope:
                AccountingPeriodLockService::SCOPE_EXPENSES,
        );
    }

    private function assertExpensePeriodOpen(
        string $context,
        mixed $date,
    ): void {
        app(
            AccountingPeriodLockService::class
        )->assertDateIsOpen(
            date: $date,
            scope:
                AccountingPeriodLockService::SCOPE_EXPENSES,
            context: $context,
        );
    }
}