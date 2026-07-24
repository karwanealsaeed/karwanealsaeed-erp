<?php

namespace App\Filament\Pages;

use App\Models\StaffMember;
use App\Models\StaffPayroll;
use App\Support\RbacPermissionRegistry;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class StaffPayrollReport extends Page
{
    protected string $view = 'filament.pages.staff-payroll-report';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Staff Payroll Report';

    protected static ?int $navigationSort = 36;

    protected static ?string $title = 'Staff Payroll Report';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user === null || ! (bool) $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->role === RbacPermissionRegistry::ROLE_MANAGER
            && $user->hasPermission('staff_payrolls_view')
            && $user->hasPermission('reports_view');
    }

    public function staffMembers(): Collection
    {
        return StaffMember::query()
            ->orderBy('name')
            ->get();
    }

    public function rows(): Collection
    {
        $query = StaffPayroll::query()
            ->with('staffMember')
            ->orderByDesc('salary_year')
            ->orderByDesc('salary_month')
            ->orderByDesc('id');

        if (request('staff_member_id')) {
            $query->where('staff_member_id', (int) request('staff_member_id'));
        }

        if (request('salary_month')) {
            $query->where('salary_month', (int) request('salary_month'));
        }

        if (request('salary_year')) {
            $query->where('salary_year', (int) request('salary_year'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        return $query->get();
    }
}
