<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BankAccountMetadataCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_account_schema_and_model_support_branch_and_swift_codes(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'bank_accounts',
                [
                    'branch_code',
                    'swift_code',
                ],
            ),
        );

        $fillable =
            (new BankAccount())->getFillable();

        $this->assertContains(
            'branch_code',
            $fillable,
        );

        $this->assertContains(
            'swift_code',
            $fillable,
        );
    }

    public function test_bank_account_admin_ui_exposes_optional_branch_and_swift_metadata(): void
    {
        $form =
            file_get_contents(
                app_path(
                    'Filament/Resources/BankAccounts/Schemas/BankAccountForm.php',
                ),
            );

        $infolist =
            file_get_contents(
                app_path(
                    'Filament/Resources/BankAccounts/Schemas/BankAccountInfolist.php',
                ),
            );

        $table =
            file_get_contents(
                app_path(
                    'Filament/Resources/BankAccounts/Tables/BankAccountsTable.php',
                ),
            );

        $this->assertIsString($form);
        $this->assertIsString($infolist);
        $this->assertIsString($table);

        $this->assertStringContainsString(
            "TextInput::make('branch_code')",
            $form,
        );

        $this->assertStringContainsString(
            "TextInput::make('swift_code')",
            $form,
        );

        $this->assertStringContainsString(
            "TextEntry::make('branch_code')",
            $infolist,
        );

        $this->assertStringContainsString(
            "TextEntry::make('swift_code')",
            $infolist,
        );

        $this->assertStringContainsString(
            "TextColumn::make('branch_code')",
            $table,
        );

        $this->assertStringContainsString(
            "TextColumn::make('swift_code')",
            $table,
        );
    }
}