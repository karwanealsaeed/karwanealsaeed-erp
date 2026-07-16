<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'name' => 'Annual Leave',
                'code' => 'ANNUAL',
                'annual_quota' => 14,
                'is_paid' => true,
                'attachment_required_after_days' => 0,
                'status' => 'Active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Casual Leave',
                'code' => 'CASUAL',
                'annual_quota' => 10,
                'is_paid' => true,
                'attachment_required_after_days' => 0,
                'status' => 'Active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SICK',
                'annual_quota' => 8,
                'is_paid' => true,
                'attachment_required_after_days' => 2,
                'status' => 'Active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UNPAID',
                'annual_quota' => 0,
                'is_paid' => false,
                'attachment_required_after_days' => 0,
                'status' => 'Active',
                'sort_order' => 4,
            ],
        ];

        foreach ($records as $record) {
            LeaveType::query()->updateOrCreate(
                ['code' => $record['code']],
                $record
            );
        }
    }
}