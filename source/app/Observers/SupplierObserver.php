<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Supplier;
use App\Services\MasterCodeGenerator;
use Illuminate\Validation\ValidationException;

final class SupplierObserver
{
    public function __construct(
        private readonly MasterCodeGenerator $codes,
    ) {
    }

    public function creating(Supplier $supplier): void
    {
        if (blank($supplier->supplier_code)) {
            $supplier->supplier_code =
                $this->codes->nextSupplierCode();
        }
    }

    public function updating(Supplier $supplier): void
    {
        if ($supplier->isDirty('supplier_code')) {
            throw ValidationException::withMessages([
                'supplier_code' =>
                    'Supplier code is generated automatically and cannot be changed.',
            ]);
        }
    }
}
