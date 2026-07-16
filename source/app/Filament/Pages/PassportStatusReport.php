<?php

namespace App\Filament\Pages;

use App\Models\VisaApplication;
use Filament\Pages\Page;

class PassportStatusReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-magnifying-glass';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 22;
    protected static ?string $navigationLabel = 'Passport Status Report';
    protected static ?string $title = 'Passport Status Report';

    protected string $view = 'filament.pages.passport-status-report';

    public ?string $passportStatus = 'All';

    public function getReportData(): array
    {
        $statuses = [
            'Pending',
            'Received',
            'Embassy',
            'Ready',
            'Returned',
        ];

        $baseQuery = VisaApplication::query()
            ->with(['customer', 'country', 'visaType']);

        $rowsQuery = VisaApplication::query()
            ->with(['customer', 'country', 'visaType'])
            ->latest();

        if ($this->passportStatus && $this->passportStatus !== 'All') {
            $rowsQuery->where('passport_status', $this->passportStatus);
        }

        $applications = $rowsQuery->get();

        $rows = [];

        foreach ($applications as $application) {
            $rows[] = [
                'application_no' => $application->application_no,
                'customer_name' => $application->customer?->name,
                'mobile' => $application->customer?->mobile,
                'passport_no' => $application->passport_no,
                'country' => $application->country?->name,
                'visa_type' => $application->visaType?->name,
                'passport_status' => $application->passport_status,
                'passport_received_date' => $application->passport_received_date,
                'passport_return_date' => $application->passport_return_date,
                'visa_status' => $application->status,
            ];
        }

        return [
            'statuses' => $statuses,
            'selected_status' => $this->passportStatus,

            'pending_count' => (clone $baseQuery)->where('passport_status', 'Pending')->count(),
            'received_count' => (clone $baseQuery)->where('passport_status', 'Received')->count(),
            'embassy_count' => (clone $baseQuery)->where('passport_status', 'Embassy')->count(),
            'ready_count' => (clone $baseQuery)->where('passport_status', 'Ready')->count(),
            'returned_count' => (clone $baseQuery)->where('passport_status', 'Returned')->count(),

            'total_passports' => (clone $baseQuery)->count(),

            'rows' => $rows,
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


