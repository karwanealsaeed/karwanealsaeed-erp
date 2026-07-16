<?php

namespace App\Models;

use App\Services\AccountingPeriodLockService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StaffPayroll extends Model
{
    protected $fillable = [
        'payroll_no',
        'staff_member_id',
        'salary_month',
        'salary_year',
        'basic_salary',
        'allowance',
        'bonus',
        'deduction',
        'advance',
        'net_salary',
        'paid_amount',
        'balance_amount',
        'payment_date',
        'payment_method',
        'status',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'advance' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (
                StaffPayroll $payroll
            ): void {
                if (blank($payroll->payroll_no)) {
                    $nextId =
                        ((int) self::max('id'))
                        + 1;

                    $payroll->payroll_no =
                        'PAY-'
                        . str_pad(
                            (string) $nextId,
                            5,
                            '0',
                            STR_PAD_LEFT
                        );
                }
            }
        );

        static::saving(
            function (
                StaffPayroll $payroll
            ): void {
                if (
                    (float) $payroll->basic_salary
                        <= 0
                    && $payroll->staffMember
                ) {
                    $payroll->basic_salary =
                        $payroll
                            ->staffMember
                            ->basic_salary;
                }

                $payroll->net_salary =
                    (float) $payroll->basic_salary
                    + (float) $payroll->allowance
                    + (float) $payroll->bonus
                    - (float) $payroll->deduction
                    - (float) $payroll->advance;

                $payroll->net_salary = max(
                    (float) $payroll->net_salary,
                    0
                );

                if (
                    (float) $payroll->paid_amount
                    > (float) $payroll->net_salary
                ) {
                    throw ValidationException::withMessages([
                        'paid_amount' =>
                            'Paid amount net salary se zyada nahi ho sakta.',
                    ]);
                }

                $payroll->balance_amount = max(
                    (float) $payroll->net_salary
                    - (float) $payroll->paid_amount,
                    0
                );

                if (
                    (float) $payroll->paid_amount
                    <= 0
                ) {
                    $payroll->status = 'Unpaid';
                } elseif (
                    (float) $payroll->balance_amount
                    <= 0
                ) {
                    $payroll->status = 'Paid';
                } else {
                    $payroll->status = 'Partial';
                }

                $payroll
                    ->assertFinancialPeriodsOpen();
            }
        );

        static::deleting(
            function (
                StaffPayroll $payroll
            ): void {
                $payroll->assertSalaryPeriodOpen(
                    month:
                        $payroll->salary_month,

                    year:
                        $payroll->salary_year,

                    context:
                        'Payroll Deletion - Salary Period',
                );

                if (
                    (float) $payroll->paid_amount
                    > 0
                ) {
                    $payroll->assertPaymentPeriodOpen(
                        date:
                            $payroll->payment_date,

                        context:
                            'Payroll Deletion - Payment Period',
                    );
                }
            }
        );
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(
            StaffMember::class
        );
    }

    public function getMonthNameAttribute(): string
    {
        return date(
            'F',
            mktime(
                0,
                0,
                0,
                (int) $this->salary_month,
                1
            )
        );
    }

    public function getPeriodTextAttribute(): string
    {
        return $this->month_name
            . ' '
            . $this->salary_year;
    }

    public function isSalaryPeriodLocked(): bool
    {
        $date = $this->salaryAccountingDate(
            month: $this->salary_month,
            year: $this->salary_year,
        );

        return app(
            AccountingPeriodLockService::class
        )->isDateLocked(
            date: $date,
            scope:
                AccountingPeriodLockService::SCOPE_PAYROLL,
        );
    }

    public function isPaymentPeriodLocked(): bool
    {
        if (
            (float) $this->paid_amount <= 0
            || blank($this->payment_date)
        ) {
            return false;
        }

        return app(
            AccountingPeriodLockService::class
        )->isDateLocked(
            date: $this->payment_date,
            scope:
                AccountingPeriodLockService::SCOPE_PAYROLL,
        );
    }

    public function isDeletionLocked(): bool
    {
        return $this->isSalaryPeriodLocked()
            || $this->isPaymentPeriodLocked();
    }

    private function assertFinancialPeriodsOpen(): void
    {
        if (! $this->exists) {
            $this->assertSalaryPeriodOpen(
                month: $this->salary_month,
                year: $this->salary_year,
                context:
                    'Payroll Creation - Salary Period',
            );

            if (
                (float) $this->paid_amount
                > 0
            ) {
                $this->assertPaymentPeriodOpen(
                    date: $this->payment_date,
                    context:
                        'Payroll Creation - Salary Payment',
                );
            }

            return;
        }

        $salaryFieldsChanged =
            $this->isDirty([
                'staff_member_id',
                'salary_month',
                'salary_year',
                'basic_salary',
                'allowance',
                'bonus',
                'deduction',
                'advance',
            ]);

        if ($salaryFieldsChanged) {
            $this->assertSalaryPeriodOpen(
                month:
                    $this->getRawOriginal(
                        'salary_month'
                    ),

                year:
                    $this->getRawOriginal(
                        'salary_year'
                    ),

                context:
                    'Payroll Editing - Original Salary Period',
            );

            $this->assertSalaryPeriodOpen(
                month: $this->salary_month,
                year: $this->salary_year,
                context:
                    'Payroll Editing - New Salary Period',
            );
        }

        $paymentFieldsChanged =
            $this->isDirty([
                'paid_amount',
                'payment_date',
                'payment_method',
            ]);

        if (! $paymentFieldsChanged) {
            return;
        }

        $originalPaidAmount =
            (float) $this->getRawOriginal(
                'paid_amount'
            );

        if ($originalPaidAmount > 0) {
            $this->assertPaymentPeriodOpen(
                date:
                    $this->getRawOriginal(
                        'payment_date'
                    ),

                context:
                    'Payroll Editing - Original Payment Period',
            );
        }

        if (
            (float) $this->paid_amount
            > 0
        ) {
            $this->assertPaymentPeriodOpen(
                date: $this->payment_date,
                context:
                    'Payroll Editing - New Payment Period',
            );
        }
    }

    private function assertSalaryPeriodOpen(
        int|string|null $month,
        int|string|null $year,
        string $context,
    ): void {
        $date = $this->salaryAccountingDate(
            month: $month,
            year: $year,
        );

        app(
            AccountingPeriodLockService::class
        )->assertDateIsOpen(
            date: $date,
            scope:
                AccountingPeriodLockService::SCOPE_PAYROLL,
            context: $context,
        );
    }

    private function assertPaymentPeriodOpen(
        string|DateTimeInterface|null $date,
        string $context,
    ): void {
        if (blank($date)) {
            throw ValidationException::withMessages([
                'payment_date' =>
                    'Paid ya Partial payroll ke liye Payment Date required hai.',
            ]);
        }

        app(
            AccountingPeriodLockService::class
        )->assertDateIsOpen(
            date: $date,
            scope:
                AccountingPeriodLockService::SCOPE_PAYROLL,
            context: $context,
        );
    }

    private function salaryAccountingDate(
        int|string|null $month,
        int|string|null $year,
    ): Carbon {
        $month = (int) $month;
        $year = (int) $year;

        if (
            $month < 1
            || $month > 12
        ) {
            throw ValidationException::withMessages([
                'salary_month' =>
                    'Valid salary month select karein.',
            ]);
        }

        if (
            $year < 2000
            || $year > 2200
        ) {
            throw ValidationException::withMessages([
                'salary_year' =>
                    'Valid salary year enter karein.',
            ]);
        }

        return Carbon::create(
            year: $year,
            month: $month,
            day: 1,
        )
            ->endOfMonth()
            ->startOfDay();
    }
}