<?php

namespace App\Services;

use App\Models\AccountHead;
use Illuminate\Validation\ValidationException;

final class BankAccountMasterValidator
{
    public function assertValidAccountHead(
        int|string|null $accountHeadId
    ): AccountHead {
        $accountHead =
            AccountHead::query()->find(
                (int) $accountHeadId
            );

        if (! $accountHead) {
            throw ValidationException::withMessages([
                'account_head_id' =>
                    'Valid Bank GL Account Head select karein.',
            ]);
        }

        $isBankHead =
            $accountHead->status === 'Active'
            && (
                $accountHead->sub_type === 'Bank'
                || str_contains(
                    strtolower(
                        (string) $accountHead->name
                    ),
                    'bank'
                )
            );

        if (! $isBankHead) {
            throw ValidationException::withMessages([
                'account_head_id' =>
                    'Bank Account sirf active Bank GL Account Head ke saath link ho sakta hai.',
            ]);
        }

        return $accountHead;
    }
}