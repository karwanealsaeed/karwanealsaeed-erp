<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'account_head_id',
        'name',
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'branch',
        'currency',
        'opening_date',
        'opening_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_balance' => 'decimal:2',
    ];

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class
        );
    }

    public function statementEntries(): HasMany
    {
        return $this->hasMany(
            BankStatementEntry::class
        );
    }

    public function getDisplayNameAttribute(): string
    {
        $label = $this->bank_name
            . ' - '
            . $this->name;

        if (filled($this->account_number)) {
            $label .= ' ('
                . $this->account_number
                . ')';
        }

        return $label;
    }
}