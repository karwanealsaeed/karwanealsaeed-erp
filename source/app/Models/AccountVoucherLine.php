<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountVoucherLine extends Model
{
    protected $fillable = [
        'account_voucher_id',
        'account_head_id',
        'description',
        'debit',
        'credit',
        'sort_order',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(
            function (
                AccountVoucherLine $line
            ): void {
                if (
                    $line->exists
                    && $line->isDirty(
                        'account_voucher_id'
                    )
                ) {
                    $originalVoucher =
                        AccountVoucher::query()
                            ->find(
                                $line->getRawOriginal(
                                    'account_voucher_id'
                                )
                            );

                    $originalVoucher
                        ?->assertLineMutationAllowed();
                }

                $voucher =
                    AccountVoucher::query()
                        ->find(
                            $line->account_voucher_id
                        );

                $voucher
                    ?->assertLineMutationAllowed();
            }
        );

        static::deleting(
            function (
                AccountVoucherLine $line
            ): void {
                $voucher =
                    AccountVoucher::query()
                        ->find(
                            $line->account_voucher_id
                        );

                $voucher
                    ?->assertLineMutationAllowed();
            }
        );

        static::saved(
            function (
                AccountVoucherLine $line
            ): void {
                $line->accountVoucher
                    ?->refreshTotals();

                $line->accountVoucher
                    ?->syncLedgerEntries();
            }
        );

        static::deleted(
            function (
                AccountVoucherLine $line
            ): void {
                $line->accountVoucher
                    ?->refreshTotals();

                $line->accountVoucher
                    ?->syncLedgerEntries();
            }
        );
    }

    public function accountVoucher(): BelongsTo
    {
        return $this->belongsTo(AccountVoucher::class);
    }

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class);
    }
}