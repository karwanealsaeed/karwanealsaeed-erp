<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Staff;
use App\Services\HrReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class HrReports extends Page
{

    use \App\Filament\Concerns\HasRbacPageAuthorization;
protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup =
        'Reports';

    protected static ?string $navigationLabel =
        'HR Reports';

    protected static ?string $title =
        'HR Reports Dashboard';

    protected static ?string $slug =
        'hr-reports';

    protected static ?int $navigationSort = 20;

    protected string $view =
        'filament.pages.hr-reports';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int|string|null $departmentId = null;

    public int|string|null $staffId = null;

    public function mount(): void
    {
        $this->dateFrom = now()
            ->startOfMonth()
            ->format('Y-m-d');

        $this->dateTo = now()
            ->endOfMonth()
            ->format('Y-m-d');
    }

    public function updatedDepartmentId(): void
    {
        $this->staffId = null;
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()
            ->startOfMonth()
            ->format('Y-m-d');

        $this->dateTo = now()
            ->endOfMonth()
            ->format('Y-m-d');

        $this->departmentId = null;
        $this->staffId = null;
    }

    public function departmentOptions(): array
    {
        return Department::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function staffOptions(): array
    {
        return Staff::query()
            ->when(
                filled($this->departmentId),
                fn ($query) => $query->where(
                    'department_id',
                    (int) $this->departmentId
                )
            )
            ->orderBy('name')
            ->get()
            ->mapWithKeys(
                fn (Staff $staff): array => [
                    $staff->id => filled(
                        $staff->staff_code
                    )
                        ? $staff->staff_code
                            . ' - '
                            . $staff->name
                        : $staff->name,
                ]
            )
            ->toArray();
    }

    public function reportData(): array
    {
        return app(HrReportService::class)->generate(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            departmentId: $this->departmentId,
            staffId: $this->staffId,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}