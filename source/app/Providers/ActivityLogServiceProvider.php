<?php

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $models = [
            \App\Models\Country::class,
            \App\Models\Airport::class,
            \App\Models\Airline::class,
            \App\Models\VisaType::class,
            \App\Models\Supplier::class,

            \App\Models\Customer::class,
            \App\Models\CustomerDocument::class,

            \App\Models\VisaApplication::class,
            \App\Models\FlightBooking::class,
            \App\Models\UmrahBooking::class,
            \App\Models\HotelBooking::class,

            \App\Models\Payment::class,
            \App\Models\FlightPayment::class,
            \App\Models\UmrahPayment::class,
            \App\Models\HotelPayment::class,

            \App\Models\Expense::class,
            \App\Models\ExpenseCategory::class,
            \App\Models\Refund::class,
            \App\Models\Invoice::class,

            \App\Models\CompanySetting::class,
            \App\Models\BankAccount::class,
            \App\Models\DatabaseBackup::class,
        ];

        foreach ($models as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $modelClass::created(function (Model $model): void {
                app(ActivityLogger::class)->created($model);
            });

            $modelClass::updated(function (Model $model): void {
                app(ActivityLogger::class)->updated($model);
            });

            $modelClass::deleted(function (Model $model): void {
                app(ActivityLogger::class)->deleted($model);
            });
        }
    }
}