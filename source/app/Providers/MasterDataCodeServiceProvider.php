<?php

declare(strict_types=1);

namespace App\Providers;

use App\Observers\CityObserver;

use App\Observers\AirportObserver;

use App\Models\City;

use App\Models\Airport;

use App\Models\Customer;
use App\Models\Supplier;
use App\Observers\CustomerObserver;
use App\Observers\SupplierObserver;
use Illuminate\Support\ServiceProvider;

final class MasterDataCodeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Customer::observe(CustomerObserver::class);
        Supplier::observe(SupplierObserver::class);

        Airport::observe(AirportObserver::class);
        City::observe(CityObserver::class);
}
}
