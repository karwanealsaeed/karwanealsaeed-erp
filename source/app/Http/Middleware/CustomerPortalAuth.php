<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('customer_portal_id')) {
            return redirect()
                ->route('customer.portal.login')
                ->with('error', 'Please login to access customer portal.');
        }

        $timeoutSeconds = 30 * 60;
        $lastActivity = (int) session('customer_portal_last_activity', time());

        if ((time() - $lastActivity) > $timeoutSeconds) {
            session()->forget([
                'customer_portal_id',
                'customer_portal_name',
                'customer_portal_last_activity',
            ]);

            return redirect()
                ->route('customer.portal.login')
                ->with('error', '30 minutes inactivity ki wajah se aap logout ho gaye hain.');
        }

        session(['customer_portal_last_activity' => time()]);

        return $next($request);
    }
}