<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Navigation\NavigationGroup;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()->brandName('Karwan E Al Saeed')
            ->renderHook(
    'panels::auth.login.form.after',
    fn (): string => view('filament.admin.login-public-link')->render(),
)
->renderHook(
    'panels::head.end',
    fn (): string => view('filament.admin.theme-head')->render(),
)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\CustomPages\Dashboard::class,
                \App\Filament\Pages\UmrahPackagePoster::class,
                \App\Filament\Pages\EmailSmtpTest::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])
            ->navigationGroups([
    NavigationGroup::make()
        ->label('Master Data')
        ->collapsed(true),

    NavigationGroup::make()
        ->label('Customers')
        ->collapsed(true),

    NavigationGroup::make()
        ->label('Finance')
        ->collapsed(true),

    NavigationGroup::make()
        ->label('Bookings')
        ->collapsed(true),

    NavigationGroup::make()
        ->label('Reports')
        ->collapsed(true),

    NavigationGroup::make()
        ->label('Settings')
        ->collapsed(true),
])
                        ->renderHook(
                'panels::head.end',
                fn (): string => '<link rel="stylesheet" href="' . asset('css/kes-filament-custom.css') . '?v=1">'
            )
                ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}


