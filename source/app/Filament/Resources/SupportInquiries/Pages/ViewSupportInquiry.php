<?php

namespace App\Filament\Resources\SupportInquiries\Pages;

use App\Filament\Resources\SupportInquiries\SupportInquiryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportInquiry extends ViewRecord
{
    protected static string $resource = SupportInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
