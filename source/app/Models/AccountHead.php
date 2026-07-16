<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountHead extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'sub_type',
        'parent_id',
        'opening_debit',
        'opening_credit',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_debit' => 'decimal:2',
        'opening_credit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (AccountHead $account): void {
            if (blank($account->code)) {
                $prefix = match ($account->type) {
                    'Asset' => 'A',
                    'Liability' => 'L',
                    'Equity' => 'E',
                    'Income' => 'I',
                    'Expense' => 'X',
                    default => 'A',
                };

                $nextId = ((int) self::max('id')) + 1;
                $account->code = $prefix . '-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AccountHead::class, 'parent_id');
    }

    public function voucherLines(): HasMany
    {
        return $this->hasMany(AccountVoucherLine::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(AccountLedger::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->code . ' - ' . $this->name;
    }

    public function getOpeningBalanceAttribute(): float
    {
        return (float) $this->opening_debit - (float) $this->opening_credit;
    }

    public function getLedgerDebitAttribute(): float
    {
        return (float) $this->ledgers()->where('status', 'Posted')->sum('debit');
    }

    public function getLedgerCreditAttribute(): float
    {
        return (float) $this->ledgers()->where('status', 'Posted')->sum('credit');
    }

    public function getBalanceAttribute(): float
    {
        return $this->opening_balance + $this->ledger_debit - $this->ledger_credit;
    }
}