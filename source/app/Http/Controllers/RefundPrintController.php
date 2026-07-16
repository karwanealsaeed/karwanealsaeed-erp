<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Refund;

class RefundPrintController extends Controller
{
    public function __invoke(Refund $refund)
    {   
        abort_unless(auth()->user()?->hasPermission('refunds.print') ?? false, 403);

        $refund->load('customer');

        $company = CompanySetting::current();

        $logoUrl = null;

        if ($company->logo) {
            $logoPath = public_path('storage/' . $company->logo);

            if (file_exists($logoPath)) {
                $logoUrl = asset('storage/' . $company->logo);
            }
        }

        return view('refunds.print', [
            'refund' => $refund,
            'company' => $company,
            'logoUrl' => $logoUrl,
        ]);
    }
}