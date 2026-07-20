<?php

namespace App\View\Components;

use App\Models\Promotion;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomerLatestOffers extends Component
{
    public function render(): View
    {
        $promotions = Promotion::query()
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view(
            'components.customer-latest-offers',
            ['promotions' => $promotions],
        );
    }
}