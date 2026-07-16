<?php

namespace App\Filament\Pages;

use App\Models\VisaApplication;
use App\Models\VisaType;
use Filament\Pages\Page;

class VisaTypeWiseReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 21;
    protected static ?string $navigationLabel = 'Visa Type Wise Report';
    protected static ?string $title = 'Visa Type Wise Report';

    protected string $view = 'filament.pages.visa-type-wise-report';

    public function getReportData(): array
    {
        $rows = [];

        $visaTypes = VisaType::orderBy('name')->get();

        foreach ($visaTypes as $visaType) {
            $query = VisaApplication::where('visa_type_id', $visaType->id);

            $totalApplications = (clone $query)->count();

            if ($totalApplications <= 0) {
                continue;
            }

            $totalSales = (clone $query)->sum('total_amount');
            $paidAmount = (clone $query)->sum('paid_amount');
            $balanceAmount = (clone $query)->sum('balance_amount');

            $draft = (clone $query)->where('status', 'Draft')->count();
            $applied = (clone $query)->where('status', 'Applied')->count();
            $processing = (clone $query)->where('status', 'Processing')->count();
            $approved = (clone $query)->where('status', 'Approved')->count();
            $rejected = (clone $query)->where('status', 'Rejected')->count();
            $delivered = (clone $query)->where('status', 'Delivered')->count();

            $rows[] = [
                'visa_type_name' => $visaType->name,
                'total_applications' => $totalApplications,
                'total_sales' => (float) $totalSales,
                'paid_amount' => (float) $paidAmount,
                'balance_amount' => (float) $balanceAmount,
                'draft' => $draft,
                'applied' => $applied,
                'processing' => $processing,
                'approved' => $approved,
                'rejected' => $rejected,
                'delivered' => $delivered,
            ];
        }

        return [
            'rows' => $rows,
            'total_visa_types' => count($rows),
            'total_applications' => collect($rows)->sum('total_applications'),
            'total_sales' => collect($rows)->sum('total_sales'),
            'total_paid' => collect($rows)->sum('paid_amount'),
            'total_balance' => collect($rows)->sum('balance_amount'),
            'total_draft' => collect($rows)->sum('draft'),
            'total_applied' => collect($rows)->sum('applied'),
            'total_processing' => collect($rows)->sum('processing'),
            'total_approved' => collect($rows)->sum('approved'),
            'total_rejected' => collect($rows)->sum('rejected'),
            'total_delivered' => collect($rows)->sum('delivered'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


