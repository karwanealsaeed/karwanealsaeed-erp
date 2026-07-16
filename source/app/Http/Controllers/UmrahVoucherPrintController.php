<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\UmrahVoucher;
use Illuminate\View\View;

class UmrahVoucherPrintController extends Controller
{
    public function __invoke(UmrahVoucher $umrahVoucher): View
    {
        abort_unless(
            (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.view') ?? false),
            403
        );

        $umrahVoucher->load([
            'customer',
            'umrahPackage.umrahGroupFlight',
            'umrahPackage.makkahHotel',
            'umrahPackage.madinaHotel',
            'umrahPackage.transportRoute',
        ]);

        return view('prints.umrah-voucher', [
            'company' => CompanySetting::current(),
            'voucher' => $umrahVoucher,
            'package' => $umrahVoucher->umrahPackage,
            'groupFlight' => $umrahVoucher->umrahPackage?->umrahGroupFlight,
        ]);
    }
}