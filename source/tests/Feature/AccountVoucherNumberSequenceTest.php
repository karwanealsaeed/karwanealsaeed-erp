<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccountVoucher;
use App\Services\MasterCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AccountVoucherNumberSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generator_self_heals_stale_sequence_and_never_reuses_deleted_number(): void
    {
        $this->insertVoucher(
            id: 24,
            voucherNo: 'AV-00024',
        );

        $this->insertVoucher(
            id: 25,
            voucherNo: 'AV-00025',
        );

        DB::table('account_vouchers')
            ->where('id', 25)
            ->delete();

        DB::table('master_code_sequences')
            ->where(
                'sequence_key',
                'account_voucher',
            )
            ->update([
                'next_value' => 1,
            ]);

        $generator =
            app(MasterCodeGenerator::class);

        $this->assertSame(
            'AV-00026',
            $generator
                ->nextAccountVoucherNumber(),
        );

        $this->assertSame(
            27,
            (int)
                DB::table(
                    'master_code_sequences',
                )
                    ->where(
                        'sequence_key',
                        'account_voucher',
                    )
                    ->value('next_value'),
        );

        $this->assertSame(
            'AV-00027',
            $generator
                ->nextAccountVoucherNumber(),
        );
    }

    public function test_account_voucher_model_uses_monotonic_sequence_after_highest_row_cleanup(): void
    {
        $this->insertVoucher(
            id: 24,
            voucherNo: 'AV-00024',
        );

        $this->insertVoucher(
            id: 25,
            voucherNo: 'AV-00025',
        );

        DB::table('account_vouchers')
            ->where('id', 25)
            ->delete();

        DB::table('master_code_sequences')
            ->where(
                'sequence_key',
                'account_voucher',
            )
            ->update([
                'next_value' => 1,
            ]);

        $voucher =
            AccountVoucher::query()
                ->create([
                    'voucher_date' =>
                        now()->toDateString(),
                    'voucher_type' =>
                        'Journal',
                    'reference_no' =>
                        'SEQUENCE-TEST-001',
                    'total_debit' =>
                        0,
                    'total_credit' =>
                        0,
                    'status' =>
                        'Draft',
                ]);

        $this->assertSame(
            'AV-00026',
            $voucher->voucher_no,
        );

        $this->assertDatabaseHas(
            'master_code_sequences',
            [
                'sequence_key' =>
                    'account_voucher',
                'next_value' =>
                    27,
            ],
        );
    }

    public function test_voucher_sequence_source_keeps_transaction_and_row_lock_contract(): void
    {
        $service =
            file_get_contents(
                app_path(
                    'Services/MasterCodeGenerator.php',
                ),
            );

        $model =
            file_get_contents(
                app_path(
                    'Models/AccountVoucher.php',
                ),
            );

        $this->assertIsString($service);
        $this->assertIsString($model);

        foreach (
            [
                'nextAccountVoucherNumber',
                "sequenceKey: 'account_voucher'",
                "prefix: 'AV'",
                'padding: 5',
                'DB::transaction',
                'lockForUpdate',
                'accountVoucherMinimumNextValue',
                'sqlite_sequence',
                'information_schema.TABLES',
            ]
            as $contract
        ) {
            $this->assertStringContainsString(
                $contract,
                $service,
            );
        }

        $this->assertStringContainsString(
            'MasterCodeGenerator::class',
            $model,
        );

        $this->assertStringNotContainsString(
            "self::max('id')",
            $model,
        );
    }

    private function insertVoucher(
        int $id,
        string $voucherNo,
    ): void {
        DB::table('account_vouchers')
            ->insert([
                'id' =>
                    $id,
                'voucher_no' =>
                    $voucherNo,
                'voucher_date' =>
                    now()->toDateString(),
                'voucher_type' =>
                    'Journal',
                'reference_no' =>
                    'SEQUENCE-SEED-'.$id,
                'party_name' =>
                    null,
                'description' =>
                    null,
                'total_debit' =>
                    0,
                'total_credit' =>
                    0,
                'status' =>
                    'Draft',
                'created_by' =>
                    null,
                'source_model' =>
                    null,
                'source_id' =>
                    null,
                'source_type' =>
                    null,
                'source_no' =>
                    null,
                'reversal_of_voucher_id' =>
                    null,
                'reversal_reason' =>
                    null,
                'created_at' =>
                    now(),
                'updated_at' =>
                    now(),
            ]);
    }
}