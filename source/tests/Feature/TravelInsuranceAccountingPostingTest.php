<?php

namespace Tests\Feature;

use App\Models\AccountHead;
use App\Models\AccountVoucher;
use App\Models\AccountVoucherLine;
use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use App\Services\AccountingAutoPostingService;
use App\Services\AccountingPeriodLockService;
use App\Services\TravelInsurancePaymentApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

class TravelInsuranceAccountingPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_cash_payment_creates_balanced_receipt_voucher(): void
    {
        $this->ensureCashAndBankAccounts();

        $payment =
            $this->createPayment(
                paymentMethod: 'Cash',
                amount: 2000,
            );

        $reviewer =
            $this->createUser();

        app(
            TravelInsurancePaymentApprovalService::class,
        )->approve(
            $payment,
            $reviewer,
        );

        $payment->refresh();

        $voucher =
            AccountVoucher::query()
                ->where(
                    'source_model',
                    TravelInsurancePayment::class,
                )
                ->where(
                    'source_id',
                    $payment->id,
                )
                ->firstOrFail();

        $this->assertSame(
            'Approved',
            $payment->status,
        );

        $this->assertSame(
            'Posted',
            $payment->posting_status,
        );

        $this->assertSame(
            $voucher->id,
            $payment->account_voucher_id,
        );

        $this->assertSame(
            'Receipt',
            $voucher->voucher_type,
        );

        $this->assertSame(
            'Posted',
            $voucher->status,
        );

        $this->assertSame(
            $payment->payment_no,
            $voucher->reference_no,
        );

        $this->assertSame(
            $payment->payment_no,
            $voucher->source_no,
        );

        $this->assertSame(
            TravelInsurancePayment::class,
            $voucher->source_model,
        );

        $this->assertSame(
            'TravelInsurancePayment',
            $voucher->source_type,
        );

        $this->assertSame(
            $payment->policy->customer->name,
            $voucher->party_name,
        );

        $this->assertSame(
            2000.0,
            (float) $voucher->total_debit,
        );

        $this->assertSame(
            2000.0,
            (float) $voucher->total_credit,
        );

        $cash =
            AccountHead::query()
                ->where(
                    'name',
                    'Cash in Hand',
                )
                ->firstOrFail();

        $income =
            AccountHead::query()
                ->where(
                    'name',
                    'Travel Insurance Sales Income',
                )
                ->firstOrFail();

        $lines =
            AccountVoucherLine::query()
                ->where(
                    'account_voucher_id',
                    $voucher->id,
                )
                ->orderBy(
                    'sort_order',
                )
                ->get();

        $this->assertCount(
            2,
            $lines,
        );

        $this->assertSame(
            $cash->id,
            $lines[0]->account_head_id,
        );

        $this->assertSame(
            2000.0,
            (float) $lines[0]->debit,
        );

        $this->assertSame(
            0.0,
            (float) $lines[0]->credit,
        );

        $this->assertSame(
            $income->id,
            $lines[1]->account_head_id,
        );

        $this->assertSame(
            0.0,
            (float) $lines[1]->debit,
        );

        $this->assertSame(
            2000.0,
            (float) $lines[1]->credit,
        );

        $this->assertSame(
            2,
            DB::table(
                'account_ledgers',
            )
                ->where(
                    'account_voucher_id',
                    $voucher->id,
                )
                ->count(),
        );
    }

    public function test_bank_transfer_debits_bank_account(): void
    {
        $this->ensureCashAndBankAccounts();

        $payment =
            $this->createPayment(
                paymentMethod: 'Bank Transfer',
                amount: 2500,
            );

        app(
            TravelInsurancePaymentApprovalService::class,
        )->approve(
            $payment,
            $this->createUser(),
        );

        $payment->refresh();

        $voucher =
            $payment->accountVoucher()
                ->firstOrFail();

        $bank =
            AccountHead::query()
                ->where(
                    'name',
                    'Bank Account',
                )
                ->firstOrFail();

        $firstLine =
            AccountVoucherLine::query()
                ->where(
                    'account_voucher_id',
                    $voucher->id,
                )
                ->orderBy(
                    'sort_order',
                )
                ->firstOrFail();

        $this->assertSame(
            $bank->id,
            $firstLine->account_head_id,
        );

        $this->assertSame(
            2500.0,
            (float) $firstLine->debit,
        );

        $this->assertSame(
            0.0,
            (float) $firstLine->credit,
        );
    }

    public function test_pending_payment_is_not_posted_directly(): void
    {
        $this->ensureCashAndBankAccounts();

        $payment =
            $this->createPayment(
                paymentMethod: 'Cash',
                amount: 1500,
            );

        app(
            AccountingAutoPostingService::class,
        )->postFromModel(
            $payment,
        );

        $this->assertDatabaseMissing(
            'account_vouchers',
            [
                'source_model' =>
                    TravelInsurancePayment::class,

                'source_id' =>
                    $payment->id,
            ],
        );

        $payment->refresh();

        $this->assertSame(
            'Pending',
            $payment->status,
        );

        $this->assertSame(
            'Pending',
            $payment->posting_status,
        );

        $this->assertNull(
            $payment->account_voucher_id,
        );
    }

    public function test_repeat_approval_does_not_duplicate_voucher(): void
    {
        $this->ensureCashAndBankAccounts();

        $payment =
            $this->createPayment(
                paymentMethod: 'Cash',
                amount: 1800,
            );

        $reviewer =
            $this->createUser();

        $service =
            app(
                TravelInsurancePaymentApprovalService::class,
            );

        $service->approve(
            $payment,
            $reviewer,
        );

        $service->approve(
            $payment,
            $reviewer,
        );

        $this->assertSame(
            1,
            AccountVoucher::query()
                ->where(
                    'source_model',
                    TravelInsurancePayment::class,
                )
                ->where(
                    'source_id',
                    $payment->id,
                )
                ->count(),
        );

        $voucher =
            AccountVoucher::query()
                ->where(
                    'source_model',
                    TravelInsurancePayment::class,
                )
                ->where(
                    'source_id',
                    $payment->id,
                )
                ->firstOrFail();

        $this->assertSame(
            2,
            AccountVoucherLine::query()
                ->where(
                    'account_voucher_id',
                    $voucher->id,
                )
                ->count(),
        );

        $payment->refresh();

        $this->assertSame(
            'Posted',
            $payment->posting_status,
        );

        $this->assertSame(
            $voucher->id,
            $payment->account_voucher_id,
        );
    }

    public function test_accounting_failure_rolls_back_approval_and_marks_payment_failed(): void
    {
        $this->ensureCashAndBankAccounts();

        $payment =
            $this->createPayment(
                paymentMethod: 'Cash',
                amount: 2200,
            );

        $this->mock(
            AccountingPeriodLockService::class,
            function (
                MockInterface $mock,
            ): void {
                $mock->shouldReceive(
                    'assertDateIsOpen',
                )
                    ->once()
                    ->andThrow(
                        ValidationException::withMessages(
                            [
                                'date' =>
                                    'General Ledger period locked hai.',
                            ],
                        ),
                    );
            },
        );

        try {
            app(
                TravelInsurancePaymentApprovalService::class,
            )->approve(
                $payment,
                $this->createUser(),
            );

            $this->fail(
                'Locked accounting period approval ko fail karna chahiye tha.',
            );
        } catch (ValidationException) {
            $this->assertTrue(
                true,
            );
        }

        $payment->refresh();

        $this->assertSame(
            'Pending',
            $payment->status,
        );

        $this->assertSame(
            'Failed',
            $payment->posting_status,
        );

        $this->assertNull(
            $payment->account_voucher_id,
        );

        $this->assertDatabaseMissing(
            'account_vouchers',
            [
                'source_model' =>
                    TravelInsurancePayment::class,

                'source_id' =>
                    $payment->id,
            ],
        );

        $payment->policy->refresh();

        $this->assertSame(
            '0.00',
            $payment->policy->paid_amount,
        );

        $this->assertSame(
            'Unpaid',
            $payment->policy->payment_status,
        );
    }

    private function ensureCashAndBankAccounts(): void
    {
        AccountHead::query()->firstOrCreate(
            [
                'name' =>
                    'Cash in Hand',
            ],
            [
                'code' =>
                    'A-10001',

                'type' =>
                    'Asset',

                'sub_type' =>
                    'Cash',

                'opening_debit' =>
                    0,

                'opening_credit' =>
                    0,

                'status' =>
                    'Active',
            ],
        );

        AccountHead::query()->firstOrCreate(
            [
                'name' =>
                    'Bank Account',
            ],
            [
                'code' =>
                    'A-10002',

                'type' =>
                    'Asset',

                'sub_type' =>
                    'Bank',

                'opening_debit' =>
                    0,

                'opening_credit' =>
                    0,

                'status' =>
                    'Active',
            ],
        );
    }

    private function createPayment(
        string $paymentMethod,
        float $amount,
    ): TravelInsurancePayment {
        return TravelInsurancePayment::query()->create(
            [
                'travel_insurance_policy_id' =>
                    $this->createPolicy()->id,

                'payment_date' =>
                    today(),

                'amount' =>
                    $amount,

                'payment_method' =>
                    $paymentMethod,

                'status' =>
                    'Pending',

                'posting_status' =>
                    'Pending',
            ],
        );
    }

    private function createPolicy(): TravelInsurancePolicy
    {
        $suffix =
            fake()->unique()->numerify(
                '######',
            );

        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        "Insurance Accounting Customer {$suffix}",

                    'mobile' =>
                        "03{$suffix}000",

                    'customer_type' =>
                        'Agent',

                    'status' =>
                        'Active',
                ],
            );

        $provider =
            InsuranceProvider::query()->create(
                [
                    'name' =>
                        "Insurance Accounting Provider {$suffix}",

                    'country' =>
                        'Pakistan',

                    'status' =>
                        'Active',
                ],
            );

        return TravelInsurancePolicy::query()->create(
            [
                'customer_id' =>
                    $customer->id,

                'insurance_provider_id' =>
                    $provider->id,

                'traveller_name' =>
                    $customer->name,

                'passport_no' =>
                    "IA{$suffix}",

                'destination_country' =>
                    'Saudi Arabia',

                'trip_type' =>
                    'Single Trip',

                'coverage_type' =>
                    'Comprehensive',

                'coverage_amount' =>
                    50000,

                'coverage_currency' =>
                    'USD',

                'travel_start_date' =>
                    today()->addDays(
                        10,
                    ),

                'travel_end_date' =>
                    today()->addDays(
                        20,
                    ),

                'policy_start_date' =>
                    today()->addDays(
                        10,
                    ),

                'policy_end_date' =>
                    today()->addDays(
                        20,
                    ),

                'premium_amount' =>
                    10000,

                'supplier_cost' =>
                    7000,

                'discount' =>
                    500,

                'tax' =>
                    250,

                'currency' =>
                    'PKR',

                'status' =>
                    'Draft',

                'approval_status' =>
                    'Pending',
            ],
        );
    }

    private function createUser(): User
    {
        return User::query()->create(
            [
                'name' =>
                    'Insurance Accounting Reviewer',

                'email' =>
                    fake()->unique()->safeEmail(),

                'password' =>
                    Hash::make(
                        'password',
                    ),

                'role' =>
                    'Admin',

                'is_active' =>
                    true,

                'permissions' =>
                    [],
            ],
        );
    }
}