<?php

namespace App\Filament\Pages;

use App\Models\Refund;
use Carbon\Carbon;
use Filament\Pages\Page;

class RefundReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-uturn-left';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 17;
    protected static ?string $navigationLabel = 'Refund Report';
    protected static ?string $title = 'Refund Report';

    protected string $view = 'filament.pages.refund-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $fromDate = $this->fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate = $this->toDate ?: now()->format('Y-m-d');

        $startDate = Carbon::parse($fromDate)->startOfDay();
        $endDate = Carbon::parse($toDate)->endOfDay();

        $refunds = Refund::with('customer')
            ->whereBetween('refund_date', [$startDate, $endDate])
            ->latest('refund_date')
            ->get();

        $serviceTypes = [
            'Visa',
            'Flight',
            'Umrah',
            'Hotel',
            'Other',
        ];

        $statuses = [
            'Pending',
            'Approved',
            'Paid',
            'Rejected',
            'Cancelled',
        ];

        $serviceRows = [];

        foreach ($serviceTypes as $serviceType) {
            $serviceRefunds = $refunds->where('service_type', $serviceType);

            $serviceRows[] = [
                'service_type' => $serviceType,
                'count' => $serviceRefunds->count(),
                'amount' => (float) $serviceRefunds->sum('refund_amount'),
            ];
        }

        $statusRows = [];

        foreach ($statuses as $status) {
            $statusRefunds = $refunds->where('status', $status);

            $statusRows[] = [
                'status' => $status,
                'count' => $statusRefunds->count(),
                'amount' => (float) $statusRefunds->sum('refund_amount'),
            ];
        }

        $rows = [];

        foreach ($refunds as $refund) {
            $rows[] = [
                'refund_no' => $refund->refund_no,
                'refund_date' => $refund->refund_date,
                'customer_name' => $refund->customer?->name,
                'mobile' => $refund->customer?->mobile,
                'service_type' => $refund->service_type,
                'service_reference' => $refund->service_reference,
                'refund_amount' => (float) $refund->refund_amount,
                'payment_method' => $refund->payment_method,
                'reference_no' => $refund->reference_no,
                'status' => $refund->status,
                'reason' => $refund->reason,
            ];
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'formatted_from_date' => $startDate->format('d M Y'),
            'formatted_to_date' => $endDate->format('d M Y'),

            'total_refunds' => $refunds->count(),
            'total_refund_amount' => (float) $refunds->sum('refund_amount'),

            'pending_count' => $refunds->where('status', 'Pending')->count(),
            'approved_count' => $refunds->where('status', 'Approved')->count(),
            'paid_count' => $refunds->where('status', 'Paid')->count(),
            'rejected_count' => $refunds->where('status', 'Rejected')->count(),
            'cancelled_count' => $refunds->where('status', 'Cancelled')->count(),

            'pending_amount' => (float) $refunds->where('status', 'Pending')->sum('refund_amount'),
            'approved_amount' => (float) $refunds->where('status', 'Approved')->sum('refund_amount'),
            'paid_amount' => (float) $refunds->where('status', 'Paid')->sum('refund_amount'),
            'rejected_amount' => (float) $refunds->where('status', 'Rejected')->sum('refund_amount'),
            'cancelled_amount' => (float) $refunds->where('status', 'Cancelled')->sum('refund_amount'),

            'service_rows' => $serviceRows,
            'status_rows' => $statusRows,
            'rows' => $rows,
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


