<?php

namespace App\Providers;

use App\Models\LoginActivity;
use App\Observers\AccountingAutoPostingObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        date_default_timezone_set('Asia/Karachi');

        foreach ([
            \App\Models\Payment::class,
            \App\Models\VisaPayment::class,
            \App\Models\FlightPayment::class,
            \App\Models\UmrahPayment::class,
            \App\Models\HotelPayment::class,
            \App\Models\Expense::class,
            \App\Models\StaffPayroll::class,
        ] as $modelClass) {
            if (class_exists($modelClass)) {
                $modelClass::observe(AccountingAutoPostingObserver::class);
            }
if (class_exists(\App\Models\CustomerPaymentUpload::class)) {
    \App\Models\CustomerPaymentUpload::observe(\App\Observers\CustomerPaymentUploadObserver::class);
}
        }

        Event::listen(Login::class, function (Login $event): void {
            try {
                if (! Schema::hasTable('login_activities')) {
                    return;
                }

                $activity = LoginActivity::create([
                    'user_id' => $event->user->id ?? null,
                    'name' => $event->user->name ?? null,
                    'email' => $event->user->email ?? null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'logged_in_at' => now(),
                    'status' => 'Login',
                ]);

                session(['login_activity_id' => $activity->id]);
            } catch (\Throwable) {
                //
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            try {
                if (! Schema::hasTable('login_activities')) {
                    return;
                }

                $activityId = session('login_activity_id');

                if ($activityId) {
                    LoginActivity::query()
                        ->where('id', $activityId)
                        ->update([
                            'logged_out_at' => now(),
                            'status' => 'Logout',
                        ]);
                }

                session()->forget('login_activity_id');
            } catch (\Throwable) {
                //
            }
        });
    }
}