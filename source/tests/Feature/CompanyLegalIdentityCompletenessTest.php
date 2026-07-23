<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompanySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CompanyLegalIdentityCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_model_and_default_company_identity_include_legal_name(): void
    {
        $this->assertTrue(
            Schema::hasColumn(
                'company_settings',
                'legal_name',
            ),
        );

        $setting =
            CompanySetting::current();

        $this->assertContains(
            'legal_name',
            $setting->getFillable(),
        );

        $this->assertNotNull(
            $setting->legal_name,
        );

        $this->assertSame(
            $setting->company_name,
            $setting->legal_name,
        );
    }

    public function test_company_settings_admin_ui_exposes_legal_name(): void
    {
        $form =
            file_get_contents(
                base_path(
                    'app/Filament/Resources/CompanySettings/Schemas/CompanySettingForm.php',
                ),
            );

        $table =
            file_get_contents(
                base_path(
                    'app/Filament/Resources/CompanySettings/Tables/CompanySettingsTable.php',
                ),
            );

        $this->assertIsString($form);
        $this->assertIsString($table);

        $this->assertStringContainsString(
            "TextInput::make('legal_name')",
            $form,
        );

        $this->assertStringContainsString(
            "->label('Legal / Registered Name')",
            $form,
        );

        $this->assertStringContainsString(
            "TextColumn::make('legal_name')",
            $table,
        );

        $this->assertStringContainsString(
            "->label('Legal Name')",
            $table,
        );
    }

    public function test_migration_backfill_and_public_legal_page_contract_are_present(): void
    {
        $migration =
            file_get_contents(
                base_path(
                    'database/migrations/2026_07_23_234500_add_legal_name_to_company_settings_table.php',
                ),
            );

        $legalPage =
            file_get_contents(
                resource_path(
                    'views/website/legal-page.blade.php',
                ),
            );

        $this->assertIsString($migration);
        $this->assertIsString($legalPage);

        $this->assertStringContainsString(
            "->string('legal_name')",
            $migration,
        );

        $this->assertStringContainsString(
            "DB::raw('company_name')",
            $migration,
        );

        $this->assertStringContainsString(
            "dropColumn(\n                        'legal_name',",
            $migration,
        );

        $this->assertStringContainsString(
            '$company->legal_name ?: $company->company_name',
            $legalPage,
        );
    }
}