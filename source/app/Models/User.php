<?php

namespace App\Models;


use App\Support\RbacPermissionRegistry;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'mobile',
        'address',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return RbacPermissionRegistry::canAccessPanel(
            $this,
            $panel,
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return RbacPermissionRegistry::userHasPermission(
            $this->permissions,
            $permission,
        );
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return RbacPermissionRegistry::userHasAnyPermission(
            $this->permissions,
            $permissions,
        );
    }
    public function canManageSettings(): bool
    {
        return $this->hasPermission('settings.manage');
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('users.manage');
    }

    public function canViewReports(): bool
    {
        return $this->hasPermission('reports.view');
    }

    public function canPrintReports(): bool
    {
        return $this->hasPermission('reports.print');
    }

    public function canDownloadReportPdf(): bool
    {
        return $this->hasPermission('reports.pdf');
    }

    public function canDownloadReportExcel(): bool
    {
        return $this->hasPermission('reports.excel');
    }

    public function canManageFinance(): bool
    {
        return $this->hasAnyPermission([
            'visa_payments.view',
            'visa_payments.create',
            'visa_payments.edit',
            'visa_payments.delete',
            'flight_payments.view',
            'flight_payments.create',
            'flight_payments.edit',
            'flight_payments.delete',
            'umrah_payments.view',
            'umrah_payments.create',
            'umrah_payments.edit',
            'umrah_payments.delete',
            'hotel_payments.view',
            'hotel_payments.create',
            'hotel_payments.edit',
            'hotel_payments.delete',
            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',
            'expense_categories.view',
            'expense_categories.create',
            'expense_categories.edit',
            'expense_categories.delete',
            'refunds.view',
            'refunds.create',
            'refunds.edit',
            'refunds.delete',
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
        ]);
    }

    public function canManageBookings(): bool
    {
        return $this->hasAnyPermission([
            'visa_applications.view',
            'visa_applications.create',
            'visa_applications.edit',
            'visa_applications.delete',
            'flight_bookings.view',
            'flight_bookings.create',
            'flight_bookings.edit',
            'flight_bookings.delete',
            'umrah_bookings.view',
            'umrah_bookings.create',
            'umrah_bookings.edit',
            'umrah_bookings.delete',
            'hotel_bookings.view',
            'hotel_bookings.create',
            'hotel_bookings.edit',
            'hotel_bookings.delete',
        ]);
    }

    public function canManageCustomers(): bool
    {
        return $this->hasAnyPermission([
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
        ]);
    }
}