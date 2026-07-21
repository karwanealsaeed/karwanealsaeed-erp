<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TravelInsurancePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TravelInsurancePaymentApprovalService
{
    public function approve(
        TravelInsurancePayment $payment,
        User $reviewer,
    ): void {
        DB::transaction(
            function () use (
                $payment,
                $reviewer,
            ): void {
                $lockedPayment =
                    TravelInsurancePayment::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $payment->getKey(),
                        );

                $this->assertReviewable(
                    $lockedPayment,
                );

                if (
                    $lockedPayment->status === 'Approved'
                ) {
                    return;
                }

                $lockedPayment->forceFill(
                    [
                        'status' =>
                            'Approved',

                        'approved_by' =>
                            $reviewer->getKey(),

                        'approved_at' =>
                            now(),
                    ],
                )->save();
            },
        );

        $payment->refresh();
    }

    public function reject(
        TravelInsurancePayment $payment,
        User $reviewer,
    ): void {
        DB::transaction(
            function () use (
                $payment,
                $reviewer,
            ): void {
                $lockedPayment =
                    TravelInsurancePayment::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $payment->getKey(),
                        );

                $this->assertReviewable(
                    $lockedPayment,
                );

                if (
                    $lockedPayment->status === 'Rejected'
                ) {
                    return;
                }

                $lockedPayment->forceFill(
                    [
                        'status' =>
                            'Rejected',

                        'approved_by' =>
                            $reviewer->getKey(),

                        'approved_at' =>
                            now(),
                    ],
                )->save();
            },
        );

        $payment->refresh();
    }

    private function assertReviewable(
        TravelInsurancePayment $payment,
    ): void {
        if (
            $payment->isAccountingLocked()
        ) {
            throw ValidationException::withMessages(
                [
                    'payment' =>
                        'Posted accounting payment approve ya reject nahi ki ja sakti.',
                ],
            );
        }
    }
}