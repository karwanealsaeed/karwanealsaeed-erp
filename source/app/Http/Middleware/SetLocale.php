<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestedLocale = $request->query('lang');

        if (
            is_string($requestedLocale)
            && in_array($requestedLocale, ['en', 'ur'], true)
        ) {
            session(['locale' => $requestedLocale]);
        }

        $locale = session('locale', config('app.locale', 'en'));

        if (! in_array($locale, ['en', 'ur'], true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}