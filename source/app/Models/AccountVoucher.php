<?php

namespace App\Models;

use App\Services\AccountingPeriodLockService;
use App\Services\AccountVoucherPostingValidator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AccountVoucher extends Model
{
    protected $fillable = [
        'voucher_no',
        'voucher_date',
        'voucher_type',
        'reference_no',
        'party_name',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'source_model',
        'source_id',
        'source_type',
        'source_no',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (AccountVoucher $voucher): void {
            if (blank($voucher->voucher_no)) {
                $nextId = ((int) self::max('id')) + 1;
                $voucher->voucher_no = 'AV-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }

            if (blank($voucher->voucher_date)) {
                $voucher->voucher_date = now()->toDateString();
            }

            if (blank($voucher->created_by) && auth()->check()) {
                $voucher->created_by = auth()->id();
            }

            if ($voucher->status === 'Posted') {
                app(
                    AccountingPeriodLockService::class
                )->assertDateIsOpen(
                    date: $voucher->voucher_date,
                    scope:
                        AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
                    context:
                        'Accounting Voucher Posting',
                );
            }
        });

        static::updating(
            function (
                AccountVoucher $voucher
            ): void {
                $accountingFieldsChanged =
                    $voucher->isDirty([
                        'voucher_no',
                        'voucher_date',
                        'voucher_type',
                        'reference_no',
                        'party_name',
                        'description',
                        'total_debit',
                        'total_credit',
                        'status',
                    ]);

                if (! $accountingFieldsChanged) {
                    return;
                }

                $periodLockService = app(
                    AccountingPeriodLockService::class
                );

                $originalStatus = (string)
                    $voucher->getRawOriginal(
                        'status'
                    );

                $newStatus = (string)
                    $voucher->status;

                if ($originalStatus === 'Posted') {
                    $periodLockService
                        ->assertDateIsOpen(
                            date:
                                $voucher->getRawOriginal(
                                    'voucher_date'
                                ),

                            scope:
                                AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,

                            context:
                                'Posted Voucher Original Period Modification',
                        );
                }

                if ($newStatus === 'Posted') {
                    $periodLockService
                        ->assertDateIsOpen(
                            date:
                                $voucher->voucher_date,

                            scope:
                                AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,

                            context:
                                'Accounting Voucher Posting or Modification',
                        );
                }
            }
        );
        static::deleting(
            function (
                AccountVoucher $voucher
            ): void {
                if ($voucher->status !== 'Posted') {
                    return;
                }

                app(
                    AccountingPeriodLockService::class
                )->assertDateIsOpen(
                    date:
                        $voucher->getRawOriginal(
                            'voucher_date'
                        )
                        ?: $voucher->voucher_date,

                    scope:
                        AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,

                    context:
                        'Posted Accounting Voucher Deletion',
                );
            
                $voucher->assertNotReconciled();
            }
        );
        static::saved(function (AccountVoucher $voucher): void {
            $voucher->refreshTotals();
            $voucher->syncLedgerEntries();
        });

        static::deleted(function (AccountVoucher $voucher): void {
            AccountLedger::query()
                ->where('account_voucher_id', $voucher->id)
                ->delete();
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountVoucherLine::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(AccountLedger::class);
    }

    public function refreshTotals(): void
    {
        $debit = (float) $this->lines()->sum('debit');
        $credit = (float) $this->lines()->sum('credit');

        if ((float) $this->total_debit !== $debit || (float) $this->total_credit !== $credit) {
            static::withoutEvents(function () use ($debit, $credit): void {
                $this->update([
                    'total_debit' => $debit,
                    'total_credit' => $credit,
                ]);
            });
        }
    }

    public function syncLedgerEntries(): void
    {
        DB::transaction(function (): void {
            AccountLedger::query()
                ->where('account_voucher_id', $this->id)
                ->delete();

            if ($this->status !== 'Posted') {
                return;
            }

            $lines =
                $this->lines()
                    ->with('accountHead')
                    ->get();

            if (
                ! app(
                    AccountVoucherPostingValidator::class
                )->isBalanced(
                    $lines->all()
                )
            ) {
                return;
            }

            $lines->each(
                function (
                    AccountVoucherLine $line
                ): void {
                    AccountLedger::create([
                        'account_head_id' =>
                            $line->account_head_id,
                        'account_voucher_id' =>
                            $this->id,
                        'account_voucher_line_id' =>
                            $line->id,
                        'entry_date' =>
                            $this->voucher_date,
                        'voucher_no' =>
                            $this->voucher_no,
                        'voucher_type' =>
                            $this->voucher_type,
                        'reference_no' =>
                            $this->reference_no,
                        'description' =>
                            $line->description
                            ?: $this->description,
                        'debit' =>
                            $line->debit,
                        'credit' =>
                            $line->credit,
                        'status' =>
                            $this->status,
                    ]);
                }
            );
        });
    }

    public function assertLineMutationAllowed(): void
    {
        if ($this->status !== 'Posted') {
            return;
        }

        app(
            AccountingPeriodLockService::class
        )->assertDateIsOpen(
            date: $this->voucher_date,

            scope:
                AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,

            context:
                'Posted Accounting Voucher Line Modification',
        );

        $this->assertNotReconciled();
    }
    public function isPeriodLocked(): bool
    {
        if ($this->status !== 'Posted') {
            return false;
        }

        return app(
            AccountingPeriodLockService::class
        )->isDateLocked(
            date: $this->voucher_date,
            scope:
                AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
        );
    }

    public function isSystemGenerated(): bool
    {
        return filled($this->source_model)
            || filled($this->source_id);
    }

    public function canBeManuallyChanged(): bool
    {
        if ($this->isSystemGenerated()) {
            return false;
        }

        if ($this->status === 'Draft') {
            return true;
        }

        if ($this->status !== 'Posted') {
            return false;
        }

        return ! $this->isPeriodLocked()
            && ! $this->isReconciled();
    }
    public function isReconciled(): bool
    {
        if (
            ! $this->exists
            || ! Schema::hasTable(
                'bank_statement_entries'
            )
        ) {
            return false;
        }

        $ledgerIds = AccountLedger::query()
            ->where(
                'account_voucher_id',
                $this->id
            )
            ->pluck('id');

        if ($ledgerIds->isEmpty()) {
            return false;
        }

        return BankStatementEntry::query()
            ->whereIn(
                'matched_account_ledger_id',
                $ledgerIds
            )
            ->exists();
    }

    public function assertNotReconciled(): void
    {
        if (! $this->isReconciled()) {
            return;
        }

        throw ValidationException::withMessages([
            'voucher' =>
                'Voucher bank reconciliation mein matched hai. Pehle related bank statement entry unmatch karein.',
        ]);
    }
    public function getIsBalancedAttribute(): bool
    {
        return round((float) $this->total_debit, 2) === round((float) $this->total_credit, 2);
    }
}