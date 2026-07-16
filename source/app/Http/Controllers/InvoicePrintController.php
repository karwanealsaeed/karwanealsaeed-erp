<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Invoice;

class InvoicePrintController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        abort_unless(auth()->user()?->hasPermission('invoices.print') ?? false, 403);

        $invoice->load('customer');

        $company = CompanySetting::current();

        $logoUrl = null;

        if ($company->logo) {
            $logoPath = public_path('storage/' . $company->logo);

            if (file_exists($logoPath)) {
                $logoUrl = asset('storage/' . $company->logo);
            }
        }

        return view('invoices.print', [
            'invoice' => $invoice,
            'company' => $company,
            'logoUrl' => $logoUrl,
        ]);
    }
}