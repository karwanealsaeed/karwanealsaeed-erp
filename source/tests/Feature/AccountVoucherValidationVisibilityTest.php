<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AccountVouchers\Pages\EditAccountVoucher;
use App\Models\AccountLedger;
use App\Models\AccountVoucher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

final class AccountVoucherValidationVisibilityTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_unbalanced_posted_save_exposes_visible_form_error_and_stays_draft(): void
    {
        $admin =
            User::factory()->create([
                'role' => 'Admin',
                'is_active' => true,
            ]);

        $debitHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-VISIBILITY-001',
                    'name' => 'Visibility Bank',
                ],
            );

        $creditHead =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Other',
                overrides: [
                    'code' => 'I-VISIBILITY-001',
                    'name' => 'Visibility Income',
                ],
            );

        $voucher =
            AccountVoucher::query()->create([
                'voucher_date' =>
                    now()->toDateString(),
                'voucher_type' =>
                    'Journal',
                'reference_no' =>
                    'TEST-VISIBILITY-001',
                'party_name' =>
                    'Validation Visibility Test',
                'description' =>
                    'Unbalanced save must show a visible error.',
                'total_debit' =>
                    0,
                'total_credit' =>
                    0,
                'status' =>
                    'Draft',
                'created_by' =>
                    $admin->id,
            ]);

        $voucher->lines()->create([
            'account_head_id' =>
                $debitHead->id,
            'description' =>
                'Debit line',
            'debit' =>
                1000,
            'credit' =>
                0,
            'sort_order' =>
                1,
        ]);

        $voucher->lines()->create([
            'account_head_id' =>
                $creditHead->id,
            'description' =>
                'Credit line',
            'debit' =>
                0,
            'credit' =>
                900,
            'sort_order' =>
                2,
        ]);

        $voucher->refreshTotals();

        $panel =
            Filament::getPanel('admin');

        $this->assertNotNull($panel);

        Filament::setCurrentPanel($panel);
        Filament::bootCurrentPanel();

        $message =
            'Posted voucher ke liye kam az kam do valid lines aur equal debit/credit totals zaroori hain. Har line mein sirf debit ya sirf credit amount enter karein.';

        Livewire::actingAs($admin)
            ->test(
                EditAccountVoucher::class,
                [
                    'record' =>
                        $voucher->getRouteKey(),
                ],
            )
            ->set(
                'data.status',
                'Posted',
            )
            ->call('save')
            ->assertHasErrors([
                'data.lines' =>
                    $message,
            ]);

        $voucher->refresh();

        $this->assertSame(
            'Draft',
            $voucher->status,
        );

        $this->assertFalse(
            AccountLedger::query()
                ->where(
                    'account_voucher_id',
                    $voucher->id,
                )
                ->exists(),
        );

        $source =
            file_get_contents(
                app_path(
                    'Filament/Resources/AccountVouchers/Pages/EditAccountVoucher.php',
                ),
            );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            "Notification::make()",
            $source,
        );
        $this->assertStringContainsString(
            "->title('Voucher post nahi ho saka')",
            $source,
        );
        $this->assertStringContainsString(
            "->persistent()",
            $source,
        );
        $this->assertStringContainsString(
            "\$this->form->getStatePath().'.lines'",
            $source,
        );
    }
}