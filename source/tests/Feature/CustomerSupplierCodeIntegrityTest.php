<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Supplier;
use App\Observers\CustomerObserver;
use App\Observers\SupplierObserver;
use App\Services\MasterCodeGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CustomerSupplierCodeIntegrityTest extends TestCase
{
    private string $previousDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDefaultConnection =
            DB::getDefaultConnection();

        config([
            'database.connections.phase13b_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('phase13b_test');
        DB::setDefaultConnection('phase13b_test');

        Schema::create(
            'customers',
            function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            },
        );

        Schema::create(
            'suppliers',
            function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            },
        );

        DB::table('customers')->insert([
            'id' => 10,
            'name' => 'Existing Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('suppliers')->insert([
            'id' => 20,
            'name' => 'Existing Supplier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration =
            require database_path(
                'migrations/2026_07_22_204547_add_customer_and_supplier_business_codes.php',
            );

        $migration->up();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists(
            'master_code_sequences',
        );
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');

        DB::disconnect('phase13b_test');
        DB::purge('phase13b_test');
        DB::setDefaultConnection(
            $this->previousDefaultConnection,
        );

        parent::tearDown();
    }

    public function test_migration_backfills_schema_and_sequences(): void
    {
        $this->assertTrue(
            Schema::hasColumn(
                'customers',
                'customer_code',
            ),
        );

        $this->assertTrue(
            Schema::hasColumn(
                'suppliers',
                'supplier_code',
            ),
        );

        $this->assertTrue(
            Schema::hasTable(
                'master_code_sequences',
            ),
        );

        $this->assertDatabaseHas(
            'customers',
            [
                'id' => 10,
                'customer_code' => 'CUS-000001',
            ],
        );

        $this->assertDatabaseHas(
            'suppliers',
            [
                'id' => 20,
                'supplier_code' => 'SUP-000001',
            ],
        );

        $this->assertDatabaseHas(
            'master_code_sequences',
            [
                'sequence_key' => 'customer',
                'next_value' => 2,
            ],
        );

        $this->assertDatabaseHas(
            'master_code_sequences',
            [
                'sequence_key' => 'supplier',
                'next_value' => 2,
            ],
        );
    }

    public function test_generator_returns_independent_sequential_codes(): void
    {
        $generator =
            app(MasterCodeGenerator::class);

        $firstCustomer =
            $generator->nextCustomerCode();

        $secondCustomer =
            $generator->nextCustomerCode();

        $supplier =
            $generator->nextSupplierCode();

        $this->assertSame(
            'CUS-000002',
            $firstCustomer,
        );

        $this->assertSame(
            'CUS-000003',
            $secondCustomer,
        );

        $this->assertSame(
            'SUP-000002',
            $supplier,
        );
    }

    public function test_customer_and_supplier_observers_generate_codes(): void
    {
        $customer = new Customer();
        $supplier = new Supplier();

        app(CustomerObserver::class)
            ->creating($customer);

        app(SupplierObserver::class)
            ->creating($supplier);

        $this->assertSame(
            'CUS-000002',
            $customer->customer_code,
        );

        $this->assertSame(
            'SUP-000002',
            $supplier->supplier_code,
        );
    }

    public function test_customer_code_is_immutable(): void
    {
        $customer = new Customer();

        $customer->setRawAttributes(
            [
                'customer_code' => 'CUS-000123',
            ],
            true,
        );

        $customer->customer_code = 'CUS-999999';

        $this->expectException(
            ValidationException::class,
        );

        app(CustomerObserver::class)
            ->updating($customer);
    }

    public function test_supplier_code_is_immutable(): void
    {
        $supplier = new Supplier();

        $supplier->setRawAttributes(
            [
                'supplier_code' => 'SUP-000123',
            ],
            true,
        );

        $supplier->supplier_code = 'SUP-999999';

        $this->expectException(
            ValidationException::class,
        );

        app(SupplierObserver::class)
            ->updating($supplier);
    }

    public function test_primary_resource_ui_exposes_searchable_codes(): void
    {
        $filamentFiles = [];

        $iterator =
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    app_path('Filament'),
                    \FilesystemIterator::SKIP_DOTS,
                ),
            );

        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && str_ends_with(
                    strtolower(
                        $file->getFilename(),
                    ),
                    '.php',
                )
            ) {
                $filamentFiles[] =
                    file_get_contents(
                        $file->getPathname(),
                    );
            }
        }

        $source =
            implode(
                "\n",
                array_filter(
                    $filamentFiles,
                    'is_string',
                ),
            );

        foreach (
            [
                "TextInput::make('customer_code')",
                "TextColumn::make('customer_code')",
                "TextInput::make('supplier_code')",
                "TextColumn::make('supplier_code')",
            ]
            as $contract
        ) {
            $this->assertStringContainsString(
                $contract,
                $source,
            );
        }
    }

    public function test_sequence_rows_remain_valid_positive_integers(): void
    {
        $sequences =
            DB::table('master_code_sequences')
                ->whereIn(
                    'sequence_key',
                    [
                        'customer',
                        'supplier',
                    ],
                )
                ->pluck(
                    'next_value',
                    'sequence_key',
                );

        $this->assertCount(2, $sequences);
        $this->assertSame(
            2,
            (int) $sequences['customer'],
        );
        $this->assertSame(
            2,
            (int) $sequences['supplier'],
        );
    }
}
