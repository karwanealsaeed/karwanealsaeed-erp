<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class HrMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Administration',
                'code' => 'ADMIN',
                'description' => 'Administration and management department.',
                'status' => 'Active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Sales',
                'code' => 'SALES',
                'description' => 'Sales and customer dealing department.',
                'status' => 'Active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Accounts',
                'code' => 'ACCOUNTS',
                'description' => 'Finance and accounting department.',
                'status' => 'Active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Visa, ticketing, Umrah and travel operations.',
                'status' => 'Active',
                'sort_order' => 4,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::query()->updateOrCreate(
                [
                    'code' => $departmentData['code'],
                ],
                $departmentData
            );
        }

        $designations = [
            [
                'department_code' => 'ADMIN',
                'name' => 'Manager',
                'code' => 'MANAGER',
                'description' => 'Office and team management.',
                'sort_order' => 1,
            ],
            [
                'department_code' => 'SALES',
                'name' => 'Sales Officer',
                'code' => 'SALES-OFFICER',
                'description' => 'Customer and agent sales handling.',
                'sort_order' => 2,
            ],
            [
                'department_code' => 'ACCOUNTS',
                'name' => 'Accountant',
                'code' => 'ACCOUNTANT',
                'description' => 'Accounts, payments and ledger management.',
                'sort_order' => 3,
            ],
            [
                'department_code' => 'OPS',
                'name' => 'Visa Officer',
                'code' => 'VISA-OFFICER',
                'description' => 'Visa applications and processing.',
                'sort_order' => 4,
            ],
            [
                'department_code' => 'OPS',
                'name' => 'Ticketing Officer',
                'code' => 'TICKETING-OFFICER',
                'description' => 'Flight booking and ticketing operations.',
                'sort_order' => 5,
            ],
            [
                'department_code' => 'OPS',
                'name' => 'Umrah Officer',
                'code' => 'UMRAH-OFFICER',
                'description' => 'Umrah bookings and package operations.',
                'sort_order' => 6,
            ],
        ];

        foreach ($designations as $designationData) {
            $department = Department::query()
                ->where('code', $designationData['department_code'])
                ->first();

            if (! $department) {
                continue;
            }

            Designation::query()->updateOrCreate(
                [
                    'code' => $designationData['code'],
                ],
                [
                    'department_id' => $department->id,
                    'name' => $designationData['name'],
                    'code' => $designationData['code'],
                    'description' => $designationData['description'],
                    'status' => 'Active',
                    'sort_order' => $designationData['sort_order'],
                ]
            );
        }
    }
}