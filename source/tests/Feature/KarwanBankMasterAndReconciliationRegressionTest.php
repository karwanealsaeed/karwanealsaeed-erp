<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\BankReconciliation;
use App\Filament\Resources\BankAccounts\BankAccountResource;
use App\Models\BankAccount;
use App\Services\BankReconciliationService;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class KarwanBankMasterAndReconciliationRegressionTest extends TestCase
{
    use DatabaseTransactions;

    private const REQUIRED_BANK_ACCOUNT_COLUMNS = [
        'account_head_id',
        'account_number',
        'account_title',
        'bank_name',
        'branch',
        'branch_code',
        'currency',
        'iban',
        'status',
        'swift_code',
    ];

    public function test_real_application_database_and_bank_schema_are_available(): void
    {
        $this->assertNotSame(
            'sqlite',
            strtolower(DB::connection()->getDriverName()),
            'Bank regression must use the configured real application database.',
        );

        foreach (
            [
                'bank_accounts',
                'account_heads',
                'bank_statement_entries',
            ] as $table
        ) {
            $this->assertTrue(
                Schema::hasTable($table),
                'Required Bank table missing: '.$table,
            );
        }

        foreach (self::REQUIRED_BANK_ACCOUNT_COLUMNS as $column) {
            $this->assertTrue(
                Schema::hasColumn('bank_accounts', $column),
                'Required bank_accounts column missing: '.$column,
            );
        }

        $indexes = Schema::getIndexes('bank_accounts');

        $this->assertTrue(
            collect($indexes)->contains(
                static fn (array $index): bool => ($index['unique'] ?? false) === true
                    && ($index['columns'] ?? []) === ['account_head_id'],
            ),
            'bank_accounts must retain unique GL-account linkage.',
        );

        $foreignKeys = Schema::getForeignKeys('bank_accounts');

        $this->assertTrue(
            collect($foreignKeys)->contains(
                static fn (array $foreignKey): bool => ($foreignKey['columns'] ?? []) === ['account_head_id']
                    && ($foreignKey['foreign_table'] ?? null) === 'account_heads'
                    && ($foreignKey['foreign_columns'] ?? []) === ['id'],
            ),
            'bank_accounts.account_head_id must reference account_heads.id.',
        );
    }

    public function test_current_bank_accounts_are_complete_unique_and_gl_linked(): void
    {
        $accounts = BankAccount::query()
            ->with('accountHead')
            ->get();

        $this->assertNotEmpty(
            $accounts,
            'At least one UAT-safe Bank Account master record is required.',
        );

        foreach ($accounts as $account) {
            foreach (
                [
                    'account_head_id',
                    'account_number',
                    'account_title',
                    'bank_name',
                    'currency',
                    'status',
                ] as $field
            ) {
                $this->assertNotNull(
                    $account->{$field},
                    'Bank Account '.$account->getKey().' has null '.$field.'.',
                );

                $this->assertNotSame(
                    '',
                    trim((string) $account->{$field}),
                    'Bank Account '.$account->getKey().' has blank '.$field.'.',
                );
            }

            $this->assertNotNull(
                $account->accountHead,
                'Bank Account '.$account->getKey().' has no valid GL account relation.',
            );
        }

        $duplicateNumbers = DB::table('bank_accounts')
            ->select('account_number')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->groupBy('account_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->assertCount(
            0,
            $duplicateNumbers,
            'Duplicate Bank Account numbers are not allowed.',
        );

        $orphanedLinks = DB::table('bank_accounts')
            ->leftJoin(
                'account_heads',
                'bank_accounts.account_head_id',
                '=',
                'account_heads.id',
            )
            ->whereNull('account_heads.id')
            ->count();

        $this->assertSame(
            0,
            $orphanedLinks,
            'Bank Account GL links must not be orphaned.',
        );
    }

    public function test_bank_account_model_and_filament_resource_contracts_exist(): void
    {
        $account = new BankAccount;

        foreach (
            [
                'account_head_id',
                'name',
                'bank_name',
                'account_title',
                'account_number',
                'iban',
                'branch',
                'branch_code',
                'swift_code',
                'currency',
                'opening_date',
                'opening_balance',
                'status',
                'notes',
            ] as $field
        ) {
            $this->assertContains(
                $field,
                $account->getFillable(),
                'BankAccount fillable contract missing: '.$field,
            );
        }

        $this->assertTrue(
            method_exists($account, 'accountHead'),
            'BankAccount accountHead relation is required.',
        );

        $this->assertSame(
            BankAccount::class,
            BankAccountResource::getModel(),
        );

        $pages = BankAccountResource::getPages();

        foreach (
            [
                'index',
                'create',
                'view',
                'edit',
            ] as $page
        ) {
            $this->assertArrayHasKey(
                $page,
                $pages,
                'BankAccountResource page missing: '.$page,
            );
        }
    }

    public function test_bank_account_form_table_and_infolist_preserve_operational_fields(): void
    {
        $form = $this->source(
            'app/Filament/Resources/BankAccounts/Schemas/BankAccountForm.php',
        );

        foreach (
            [
                "Select::make('account_head_id')",
                "TextInput::make('bank_name')",
                "TextInput::make('account_title')",
                "TextInput::make('account_number')",
                "TextInput::make('iban')",
                "TextInput::make('branch')",
                "TextInput::make('branch_code')",
                "TextInput::make('swift_code')",
                "TextInput::make('currency')",
                "Select::make('status')",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $form,
                'Bank Account form marker missing: '.$marker,
            );
        }

        $table = $this->source(
            'app/Filament/Resources/BankAccounts/Tables/BankAccountsTable.php',
        );

        foreach (
            [
                "TextColumn::make('bank_name')",
                "TextColumn::make('name')",
                "TextColumn::make('account_number')",
                "TextColumn::make('iban')",
                "TextColumn::make('branch_code')",
                "TextColumn::make('swift_code')",
                "TextColumn::make('accountHead.code')",
                "TextColumn::make('accountHead.name')",
                "TextColumn::make('opening_date')",
                "TextColumn::make('opening_balance')",
                "'statement_entries_count'",
                "TextColumn::make('status')",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $table,
                'Bank Account table marker missing: '.$marker,
            );
        }

        $infolist = $this->source(
            'app/Filament/Resources/BankAccounts/Schemas/BankAccountInfolist.php',
        );

        foreach (
            [
                "TextEntry::make('bank_name')",
                "TextEntry::make('account_number')",
                "TextEntry::make('account_title')",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $infolist,
                'Bank Account infolist marker missing: '.$marker,
            );
        }
    }

    public function test_bank_account_crud_and_reconciliation_routes_are_authenticated(): void
    {
        $account = BankAccount::query()->firstOrFail();

        $contracts = [
            [
                'filament.admin.resources.bank-accounts.index',
                [],
            ],
            [
                'filament.admin.resources.bank-accounts.create',
                [],
            ],
            [
                'filament.admin.resources.bank-accounts.view',
                ['record' => $account],
            ],
            [
                'filament.admin.resources.bank-accounts.edit',
                ['record' => $account],
            ],
            [
                'filament.admin.pages.bank-reconciliation',
                [],
            ],
        ];

        foreach ($contracts as [$name, $parameters]) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull(
                $route,
                'Protected Bank route missing: '.$name,
            );

            $this->assertContains(
                FilamentAuthenticate::class,
                $route->gatherMiddleware(),
                'Filament authentication middleware missing: '.$name,
            );

            $response = $this->get(
                route($name, $parameters, false),
            );

            $this->assertTrue(
                $response->isRedirect(),
                'Unauthenticated Bank route must redirect: '.$name,
            );
        }
    }

    public function test_bank_statement_entry_and_reconciliation_page_are_wired_to_bank_master(): void
    {
        $pageSource = $this->source(
            'app/Filament/Pages/BankReconciliation.php',
        );

        foreach (
            [
                'BankAccount::query()',
                'BankReconciliationService::class',
                'bankAccountOptions',
                'bankAccountId',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $pageSource,
                'Bank Reconciliation page marker missing: '.$marker,
            );
        }

        $this->assertTrue(
            class_exists(BankReconciliation::class),
        );

        $statementForm = $this->source(
            'app/Filament/Resources/BankStatementEntries/Schemas/BankStatementEntryForm.php',
        );

        foreach (
            [
                'BankAccount::query()',
                "'reconciliation_status'",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $statementForm,
                'Bank Statement Entry marker missing: '.$marker,
            );
        }
    }

    public function test_bank_reconciliation_service_contains_real_domain_logic(): void
    {
        $this->assertTrue(
            class_exists(BankReconciliationService::class),
        );

        $reflection = new ReflectionClass(
            BankReconciliationService::class,
        );

        $publicMethods = collect(
            $reflection->getMethods(
                \ReflectionMethod::IS_PUBLIC,
            ),
        )
            ->reject(
                static fn (\ReflectionMethod $method): bool => $method->isConstructor()
                    || $method->getDeclaringClass()->getName()
                        !== BankReconciliationService::class,
            );

        $this->assertNotEmpty(
            $publicMethods,
            'BankReconciliationService must expose reconciliation behaviour.',
        );

        $serviceSource = $this->source(
            'app/Services/BankReconciliationService.php',
        );

        $this->assertTrue(
            str_contains($serviceSource, 'BankStatementEntry')
            || str_contains($serviceSource, 'bank_statement_entries'),
            'Reconciliation service must use Bank Statement entries.',
        );

        $this->assertTrue(
            str_contains($serviceSource, 'BankAccount')
            || str_contains($serviceSource, 'bankAccountId'),
            'Reconciliation service must use the selected Bank Account.',
        );

        $this->assertTrue(
            str_contains($serviceSource, 'account_head_id')
            || str_contains($serviceSource, 'accountHead'),
            'Reconciliation service must preserve GL-account linkage.',
        );
    }

    private function source(string $relativePath): string
    {
        $path = base_path($relativePath);

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertIsString(
            $content,
            'Unable to read source file: '.$relativePath,
        );

        return $content;
    }
}
