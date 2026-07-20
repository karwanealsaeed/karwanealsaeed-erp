<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPromotionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $customer = $request->attributes->get('customer');

        if (! $customer instanceof Customer) {
            $customer = Customer::query()->findOrFail(
                (int) $request->session()->get(
                    'customer_id',
                ),
            );
        }

        $promotions = Promotion::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        return view('customer-portal.promotions', [
            'customer' => $customer,
            'promotions' => $promotions,
        ]);
    }
}