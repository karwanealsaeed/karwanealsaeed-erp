<?php

namespace App\Services;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;

final class AccountVoucherPostingValidator
{
    public function isBalanced(array $lines): bool
    {
        if (count($lines) < 2) {
            return false;
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            if ($line instanceof Arrayable) {
                $line = $line->toArray();
            } elseif (is_object($line)) {
                $line = get_object_vars($line);
            }

            if (! is_array($line)) {
                return false;
            }

            $debit =
                round(
                    (float) ($line['debit'] ?? 0),
                    2
                );

            $credit =
                round(
                    (float) ($line['credit'] ?? 0),
                    2
                );

            if (
                $debit < 0
                || $credit < 0
                || ($debit > 0 && $credit > 0)
                || ($debit <= 0 && $credit <= 0)
            ) {
                return false;
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return $totalDebit > 0
            && $totalCredit > 0
            && round($totalDebit, 2)
                === round($totalCredit, 2);
    }

    public function assertCanPost(array $lines): void
    {
        if ($this->isBalanced($lines)) {
            return;
        }

        throw ValidationException::withMessages([
            'lines' =>
                'Posted voucher ke liye kam az kam do valid lines aur equal debit/credit totals zaroori hain. Har line mein sirf debit ya sirf credit amount enter karein.',
        ]);
    }
}