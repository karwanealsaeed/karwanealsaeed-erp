<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAuth
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response|RedirectResponse {
        $customerId =
            (int) $request->session()
                ->get(
                    'customer_portal_id',
                    0,
                );

        if ($customerId <= 0) {
            return $this->logoutResponse(
                $request,
                'Please login to access customer portal.',
            );
        }

        $lastActivity =
            (int) $request->session()
                ->get(
                    'customer_portal_last_activity',
                    time(),
                );

        $timeoutSeconds =
            30 * 60;

        if (
            (
                time()
                - $lastActivity
            )
            > $timeoutSeconds
        ) {
            return $this->logoutResponse(
                $request,
                '30 minutes inactivity ki wajah se aap logout ho gaye hain.',
            );
        }

        $customer =
            Customer::query()
                ->find(
                    $customerId,
                );

        $isValidPortalCustomer =
            $customer
            && (
                $customer->customer_type
                ?? 'Customer'
            )
            === 'Agent'
            && (
                $customer->status
                ?? 'Active'
            )
            === 'Active'
            && filled(
                $customer->portal_password,
            );

        if (! $isValidPortalCustomer) {
            return $this->logoutResponse(
                $request,
                'Portal access available nahi hai. Office se contact karein.',
            );
        }

        $request->session()
            ->put(
                'customer_portal_name',
                $customer->name,
            );

        $request->session()
            ->put(
                'customer_portal_last_activity',
                time(),
            );

        return $next(
            $request,
        );
    }

    private function logoutResponse(
        Request $request,
        string $message,
    ): RedirectResponse {
        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        return redirect()
            ->route(
                'customer.portal.login',
            )
            ->with(
                'error',
                $message,
            );
    }
}