<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AccountVoucher;
use App\Models\TravelInsurancePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class TravelInsurancePaymentApprovalService
{
    public function approve(
        TravelInsurancePayment $payment,
        User $reviewer,
    ): void {
        try {
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

                    if (
                        $lockedPayment->status ===
                            'Approved'
                    ) {
                        if (
                            $lockedPayment
                                ->isAccountingLocked()
                        ) {
                            return;
                        }
                    }

                    $this->assertReviewable(
                        $lockedPayment,
                    );

                    if (
                        $lockedPayment->status !==
                        'Approved'
                    ) {
                        $lockedPayment->forceFill(
                            [
                                'status' =>
                                    'Approved',

                                'approved_by' =>
                                    $reviewer->getKey(),

                                'approved_at' =>
                                    now(),

                                'posting_status' =>
                                    'Pending',

                                'account_voucher_id' =>
                                    null,
                            ],
                        )->save();
                    }

                    app(
                        AccountingAutoPostingService::class,
                    )->postFromModel(
                        $lockedPayment->fresh(),
                    );

                    $voucher =
                        AccountVoucher::query()
                            ->where(
                                'source_model',
                                TravelInsurancePayment::class,
                            )
                            ->where(
                                'source_id',
                                $lockedPayment->getKey(),
                            )
                            ->first();

                    if (! $voucher) {
                        throw ValidationException::withMessages(
                            [
                                'payment' =>
                                    'Insurance payment ka accounting voucher create nahi hua.',
                            ],
                        );
                    }

                    $lockedPayment->forceFill(
                        [
                            'account_voucher_id' =>
                                $voucher->getKey(),

                            'posting_status' =>
                                'Posted',
                        ],
                    )->saveQuietly();
                },
            );
        } catch (Throwable $exception) {
            $failedPayment =
                TravelInsurancePayment::query()
                    ->find(
                        $payment->getKey(),
                    );

            if ($failedPayment) {
                if (
                    ! $failedPayment
                        ->isAccountingLocked()
                ) {
                    $failedPayment->forceFill(
                        [
                            'posting_status' =>
                                'Failed',

                            'account_voucher_id' =>
                                null,
                        ],
                    )->saveQuietly();
                }
            }

            throw $exception;
        }

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

                if (
                    $lockedPayment->status ===
                    'Rejected'
                ) {
                    return;
                }

                $this->assertReviewable(
                    $lockedPayment,
                );

                app(
                    AccountingAutoPostingService::class,
                )->removeForModel(
                    $lockedPayment,
                );

                $lockedPayment->forceFill(
                    [
                        'status' =>
                            'Rejected',

                        'approved_by' =>
                            $reviewer->getKey(),

                        'approved_at' =>
                            now(),

                        'posting_status' =>
                            'Pending',

                        'account_voucher_id' =>
                            null,
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