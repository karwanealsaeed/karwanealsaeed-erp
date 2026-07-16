<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\StaffPayroll;
use Illuminate\View\View;

class StaffPayrollPrintController extends Controller
{
    public function __invoke(StaffPayroll $staffPayroll): View
    {
        abort_unless(auth()->check(), 403);

        $staffPayroll->load('staffMember');

        return view('prints.staff-payroll', [
            'company' => CompanySetting::current(),
            'payroll' => $staffPayroll,
        ]);
    }
}