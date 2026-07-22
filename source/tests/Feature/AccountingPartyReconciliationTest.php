<?php

namespace Tests\Feature;

use App\Services\UnifiedCustomerStatementService;
use App\Services\UnifiedSupplierStatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class AccountingPartyReconciliationTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_customer_statement_reconciles_invoice_receipt_and_balance(): void
    {
        $customerId =
            $this->insertAccountingRow(
                'customers',
                [
                    'customer_code' => 'CUS-900001',
                'name' => 'Regression Customer',
                    'mobile' => '03000000000',
                    'whatsapp' => '03000000000',
                    'email' =>
                        'regression-customer@example.test',
                    'country' => 'Pakistan',
                    'customer_type' => 'Regular',
                    'status' => 'Active',
                ]
            );

        $invoiceId =
            $this->insertAccountingRow(
                'invoices',
                [
                    'invoice_no' => 'INV-REG-001',
                    'customer_id' => $customerId,
                    'invoice_date' => '2026-02-01',
                    'due_date' => '2026-02-28',
                    'service_type' => 'Other',
                    'service_reference' => 'REG-001',
                    'sub_total' => 1000,
                    'discount' => 0,
                    'tax' => 0,
                    'total_amount' => 1000,
                    'paid_amount' => 400,
                    'balance_amount' => 600,
                    'status' => 'Partially Paid',
                    'notes' => 'Regression fixture',
                ]
            );

        $this->insertAccountingRow(
            'customer_payment_uploads',
            [
                'customer_id' => $customerId,
                'upload_no' => 'CPU-REG-001',
                'service_type' => 'Other',
                'reference_no' => 'REG-001',
                'receipt_type' => 'Invoice Payment',
                'invoice_id' => $invoiceId,
                'amount' => 400,
                'payment_method' => 'Cash',
                'payment_date' => '2026-02-10',
                'status' => 'Approved',
                'approved_at' => now(),
                'notes' => 'Regression fixture',
            ]
        );

        $report =
            app(
                UnifiedCustomerStatementService::class
            )->generate(
                customerId: $customerId,
                dateFrom: '2026-02-01',
                dateTo: '2026-02-28',
                serviceType: 'all'
            );

        $this->assertTrue($report['database_ready']);
        $this->assertFalse($report['customer_required']);
        $this->assertSame(
            $customerId,
            $report['customer']['id']
        );

        $this->assertEqualsWithDelta(
            1000,
            $report['stats']['period_debit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            400,
            $report['stats']['period_credit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            600,
            $report['stats']['closing_balance'],
            0.01
        );

        $this->assertEqualsWithDelta(
            600,
            $report['stats']['amount_receivable'],
            0.01
        );

        $this->assertSame(
            1,
            $report['stats']['invoice_count']
        );

        $this->assertSame(
            1,
            $report['stats']['receipt_count']
        );
    }

    public function test_supplier_statement_reconciles_bill_payment_and_payable(): void
    {
        $supplierId =
            $this->insertAccountingRow(
                'suppliers',
                [
                    'supplier_code' => 'SUP-900001',
                'name' => 'Regression Supplier',
                    'company_name' =>
                        'Regression Supplier Company',
                    'mobile' => '03000000001',
                    'whatsapp' => '03000000001',
                    'email' =>
                        'regression-supplier@example.test',
                    'country' => 'Pakistan',
                    'supplier_type' => 'Other',
                    'status' => 'Active',
                ]
            );

        $billId =
            $this->insertAccountingRow(
                'supplier_bills',
                [
                    'bill_no' => 'SB-REG-001',
                    'supplier_id' => $supplierId,
                    'supplier_invoice_no' =>
                        'SUP-INV-REG-001',
                    'bill_date' => '2026-02-01',
                    'due_date' => '2026-02-28',
                    'service_type' => 'Other',
                    'reference_no' => 'REG-SUP-001',
                    'description' => 'Regression fixture',
                    'total_amount' => 1000,
                    'paid_amount' => 400,
                    'balance_amount' => 600,
                    'status' => 'Partially Paid',
                    'approval_status' => 'Approved',
                ]
            );

        $this->insertAccountingRow(
            'supplier_payments',
            [
                'payment_no' => 'SP-REG-001',
                'supplier_bill_id' => $billId,
                'supplier_id' => $supplierId,
                'payment_date' => '2026-02-10',
                'amount' => 400,
                'payment_method' => 'Cash',
                'reference_no' => 'REG-SUP-001',
                'service_type' => 'Other',
                'status' => 'Approved',
                'approval_status' => 'Approved',
                'notes' => 'Regression fixture',
            ]
        );

        $report =
            app(
                UnifiedSupplierStatementService::class
            )->generate(
                supplierId: $supplierId,
                dateFrom: '2026-02-01',
                dateTo: '2026-02-28',
                serviceType: 'all'
            );

        $this->assertTrue($report['database_ready']);
        $this->assertFalse($report['supplier_required']);
        $this->assertSame(
            $supplierId,
            $report['supplier']['id']
        );

        $this->assertEqualsWithDelta(
            400,
            $report['stats']['period_debit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            1000,
            $report['stats']['period_credit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            600,
            abs(
                (float) $report['stats']['closing_balance']
            ),
            0.01
        );

        $this->assertSame(
            1,
            $report['stats']['bill_count']
        );

        $this->assertSame(
            1,
            $report['stats']['payment_count']
        );

        $this->assertCount(
            2,
            $report['rows']
        );
    }
}