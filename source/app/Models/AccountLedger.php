<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountLedger extends Model
{
    protected $fillable = [
        'account_head_id',
        'account_voucher_id',
        'account_voucher_line_id',
        'entry_date',
        'voucher_no',
        'voucher_type',
        'reference_no',
        'description',
        'debit',
        'credit',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class);
    }

    public function accountVoucher(): BelongsTo
    {
        return $this->belongsTo(AccountVoucher::class);
    }

    public function voucherLine(): BelongsTo
    {
        return $this->belongsTo(AccountVoucherLine::class, 'account_voucher_line_id');
    }
}