<?php

namespace Database\Seeders;

use App\Models\AccountHead;
use Illuminate\Database\Seeder;

class AccountingDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => 'A-10001', 'name' => 'Cash in Hand', 'type' => 'Asset', 'sub_type' => 'Cash'],
            ['code' => 'A-10002', 'name' => 'Bank Account', 'type' => 'Asset', 'sub_type' => 'Bank'],
            ['code' => 'A-10003', 'name' => 'Accounts Receivable', 'type' => 'Asset', 'sub_type' => 'Receivable'],

            ['code' => 'L-20001', 'name' => 'Accounts Payable', 'type' => 'Liability', 'sub_type' => 'Payable'],
            ['code' => 'E-30001', 'name' => 'Owner Capital', 'type' => 'Equity', 'sub_type' => 'Capital'],

            ['code' => 'I-40001', 'name' => 'Visa Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40002', 'name' => 'Flight Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40003', 'name' => 'Umrah Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40004', 'name' => 'Hotel Sales Income', 'type' => 'Income', 'sub_type' => 'Sales'],
            ['code' => 'I-40005', 'name' => 'Service Charges Income', 'type' => 'Income', 'sub_type' => 'Sales'],

            ['code' => 'X-50001', 'name' => 'Office Expense', 'type' => 'Expense', 'sub_type' => 'Office'],
            ['code' => 'X-50002', 'name' => 'Salary Expense', 'type' => 'Expense', 'sub_type' => 'Salary'],
            ['code' => 'X-50003', 'name' => 'Rent Expense', 'type' => 'Expense', 'sub_type' => 'Rent'],
            ['code' => 'X-50004', 'name' => 'Marketing Expense', 'type' => 'Expense', 'sub_type' => 'Marketing'],
            ['code' => 'X-50005', 'name' => 'Miscellaneous Expense', 'type' => 'Expense', 'sub_type' => 'Misc'],
        ];

        foreach ($accounts as $account) {
            AccountHead::query()->firstOrCreate(
                ['code' => $account['code']],
                $account + [
                    'opening_debit' => 0,
                    'opening_credit' => 0,
                    'status' => 'Active',
                ]
            );
        }
    }
}