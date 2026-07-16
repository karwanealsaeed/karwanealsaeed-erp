<?php

namespace App\Observers;

use App\Models\CustomerPaymentUpload;
use App\Services\CustomerPaymentUploadPostingService;

class CustomerPaymentUploadObserver
{
    public function saved(CustomerPaymentUpload $upload): void
    {
        app(CustomerPaymentUploadPostingService::class)->sync($upload);
    }

    public function deleted(CustomerPaymentUpload $upload): void
    {
        app(CustomerPaymentUploadPostingService::class)->remove($upload);
    }
}