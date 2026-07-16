<?php

namespace App\Models;

use App\Services\AccountingPeriodLockService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class BankStatementEntry extends Model
{
    protected $fillable = [
        'entry_no',
        'bank_account_id',
        'statement_date',
        'reference_no',
        'cheque_no',
        'description',
        'deposit',
        'withdrawal',
        'running_balance',
        'reconciliation_status',
        'matched_account_ledger_id',
        'matched_by',
        'matched_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'deposit' => 'decimal:2',
        'withdrawal' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'matched_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (
            BankStatementEntry $entry
        ): void {
            $entry->statement_date ??= today();

            $entry->reconciliation_status ??=
                'Unmatched';

            if (
                blank($entry->created_by)
                && auth()->check()
            ) {
                $entry->created_by = auth()->id();
            }
        });

        static::saving(function (
            BankStatementEntry $entry
        ): void {
            $entry->deposit = round(
                max(
                    0,
                    (float) ($entry->deposit ?? 0)
                ),
                2
            );

            $entry->withdrawal = round(
                max(
                    0,
                    (float) ($entry->withdrawal ?? 0)
                ),
                2
            );

            if (
                $entry->deposit <= 0
                && $entry->withdrawal <= 0
            ) {
                throw ValidationException::withMessages([
                    'deposit' => 'Deposit ya Withdrawal me se ek amount enter karein.',
                ]);
            }

            if (
                $entry->deposit > 0
                && $entry->withdrawal > 0
            ) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Ek entry me Deposit aur Withdrawal dono enter nahi ho sakte.',
                ]);
            }

            if (
                $entry->matched_account_ledger_id
            ) {
                $entry->validateLedgerMatch();

                $entry->reconciliation_status =
                    'Matched';
            } elseif (
                $entry->reconciliation_status
                === 'Matched'
            ) {
                $entry->reconciliation_status =
                    'Unmatched';

                $entry->matched_by = null;
                $entry->matched_at = null;
            }

            if (
                $entry->reconciliation_status
                === 'Ignored'
            ) {
                $entry->matched_account_ledger_id =
                    null;

                $entry->matched_by = null;
                $entry->matched_at = null;
            }

            $entry->assertMutationPeriodOpen();
        });

        static::deleting(
            function (
                BankStatementEntry $entry
            ): void {
                if (
                    $entry->matched_account_ledger_id
                ) {
                    throw ValidationException::withMessages([
                        'reconciliation_status' =>
                            'Matched bank statement entry delete nahi ho sakti. Pehle entry unmatch karein.',
                    ]);
                }

                $entry->assertStatementPeriodOpen(
                    date:
                        $entry->getRawOriginal(
                            'statement_date'
                        )
                        ?: $entry->statement_date,

                    context:
                        'Bank Statement Entry Deletion',
                );
            }
        );
        static::created(function (
            BankStatementEntry $entry
        ): void {
            if (blank($entry->entry_no)) {
                $entry->updateQuietly([
                    'entry_no' => 'BSE-' . str_pad(
                        (string) $entry->id,
                        7,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        });
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class
        );
    }

    public function matchedLedger(): BelongsTo
    {
        return $this->belongsTo(
            AccountLedger::class,
            'matched_account_ledger_id'
        );
    }

    public function matcher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'matched_by'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function getTransactionTypeAttribute(): string
    {
        return (float) $this->deposit > 0
            ? 'Deposit'
            : 'Withdrawal';
    }

    public function getAmountAttribute(): float
    {
        return (float) $this->deposit > 0
            ? (float) $this->deposit
            : (float) $this->withdrawal;
    }

    public function isPeriodLocked(): bool
    {
        return app(
            AccountingPeriodLockService::class
        )->isDateLocked(
            date: $this->statement_date,
            scope:
                AccountingPeriodLockService::SCOPE_BANK,
        );
    }

    private function assertMutationPeriodOpen(): void
    {
        $financialFields = [
            'bank_account_id',
            'statement_date',
            'reference_no',
            'cheque_no',
            'description',
            'deposit',
            'withdrawal',
            'running_balance',
            'reconciliation_status',
            'matched_account_ledger_id',
            'matched_by',
            'matched_at',
        ];

        if (! $this->exists) {
            $this->assertStatementPeriodOpen(
                date: $this->statement_date,
                context:
                    'Bank Statement Entry Creation',
            );

            return;
        }

        if (! $this->isDirty($financialFields)) {
            return;
        }

        $this->assertStatementPeriodOpen(
            date:
                $this->getRawOriginal(
                    'statement_date'
                ),

            context:
                'Bank Reconciliation Original Period Modification',
        );

        $this->assertStatementPeriodOpen(
            date: $this->statement_date,
            context:
                'Bank Reconciliation New Period Modification',
        );
    }

    private function assertStatementPeriodOpen(
        string|DateTimeInterface|null $date,
        string $context,
    ): void {
        app(
            AccountingPeriodLockService::class
        )->assertDateIsOpen(
            date: $date,
            scope:
                AccountingPeriodLockService::SCOPE_BANK,
            context: $context,
        );
    }
    private function validateLedgerMatch(): void
    {
        $bankAccount = BankAccount::query()
            ->find($this->bank_account_id);

        $ledger = AccountLedger::query()
            ->find($this->matched_account_ledger_id);

        if (! $bankAccount || ! $ledger) {
            throw ValidationException::withMessages([
                'matched_account_ledger_id' =>
                    'Selected GL ledger entry nahi mili.',
            ]);
        }

        if (
            (int) $ledger->account_head_id
            !== (int) $bankAccount->account_head_id
        ) {
            throw ValidationException::withMessages([
                'matched_account_ledger_id' =>
                    'Selected GL entry is bank account ke Account Head se related nahi hai.',
            ]);
        }

        if ($ledger->status !== 'Posted') {
            throw ValidationException::withMessages([
                'matched_account_ledger_id' =>
                    'Sirf Posted GL entry reconcile ki ja sakti hai.',
            ]);
        }

        $statementAmount = round(
            $this->amount,
            2
        );

        $ledgerAmount = (float) $this->deposit > 0
            ? round((float) $ledger->debit, 2)
            : round((float) $ledger->credit, 2);

        if ($statementAmount !== $ledgerAmount) {
            throw ValidationException::withMessages([
                'matched_account_ledger_id' =>
                    'Statement aur GL entry amounts same honi chahiye.',
            ]);
        }
    }
}