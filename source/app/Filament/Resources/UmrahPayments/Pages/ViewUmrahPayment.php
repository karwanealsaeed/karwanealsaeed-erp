<?php

namespace App\Filament\Resources\UmrahPayments\Pages;

use App\Filament\Resources\UmrahPayments\UmrahPaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUmrahPayment extends ViewRecord
{
    protected static string $resource = UmrahPaymentResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
