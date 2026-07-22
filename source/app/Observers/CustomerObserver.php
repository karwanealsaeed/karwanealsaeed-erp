<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Customer;
use App\Services\MasterCodeGenerator;
use Illuminate\Validation\ValidationException;

final class CustomerObserver
{
    public function __construct(
        private readonly MasterCodeGenerator $codes,
    ) {
    }

    public function creating(Customer $customer): void
    {
        if (blank($customer->customer_code)) {
            $customer->customer_code =
                $this->codes->nextCustomerCode();
        }
    }

    public function updating(Customer $customer): void
    {
        if ($customer->isDirty('customer_code')) {
            throw ValidationException::withMessages([
                'customer_code' =>
                    'Customer code is generated automatically and cannot be changed.',
            ]);
        }
    }
}
