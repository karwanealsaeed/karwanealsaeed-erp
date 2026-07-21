<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TravelInsurancePolicy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPortalTravelInsuranceController extends Controller
{
    public function __invoke(
        Request $request,
    ): View {
        $customerId =
            (int) $request->session()
                ->get(
                    'customer_portal_id',
                    0,
                );

        abort_unless(
            $customerId > 0,
            403,
        );

        $baseQuery =
            TravelInsurancePolicy::query()
                ->where(
                    'customer_id',
                    $customerId,
                )
                ->where(
                    'approval_status',
                    'Approved',
                );

        $summary = [
            'total' =>
                (clone $baseQuery)
                    ->count(),

            'active' =>
                (clone $baseQuery)
                    ->where(
                        'status',
                        'Active',
                    )
                    ->count(),

            'expiring_soon' =>
                (clone $baseQuery)
                    ->whereDate(
                        'policy_end_date',
                        '>=',
                        today(),
                    )
                    ->whereDate(
                        'policy_end_date',
                        '<=',
                        today()->addDays(
                            30,
                        ),
                    )
                    ->count(),

            'outstanding' =>
                round(
                    (float) (
                        clone $baseQuery
                    )->sum(
                        'balance_amount',
                    ),
                    2,
                ),
        ];

        $policies =
            $baseQuery
                ->with(
                    [
                        'provider',
                        'invoice',
                        'payments' =>
                            function (
                                $query,
                            ): void {
                                $query
                                    ->where(
                                        'status',
                                        'Approved',
                                    )
                                    ->orderByDesc(
                                        'payment_date',
                                    )
                                    ->orderByDesc(
                                        'id',
                                    );
                            },
                    ],
                )
                ->orderByDesc(
                    'policy_start_date',
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    12,
                    [
                        '*',
                    ],
                    'insurance_page',
                )
                ->withQueryString();

        return view(
            'customer-portal.travel-insurance',
            [
                'policies' =>
                    $policies,

                'summary' =>
                    $summary,
            ],
        );
    }
}