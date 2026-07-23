<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Filament\Panel;

final class RbacPermissionRegistry
{
    public const ROLE_ADMIN = 'Admin';

    public const ROLE_MANAGER = 'Manager';

    public const ROLE_ACCOUNTANT = 'Accountant';

    public const ROLE_SALES_STAFF = 'Sales Staff';

    public const ACCOUNTING_MANAGE = 'accounting_manage';

    private const ROLE_OPTIONS = [
        self::ROLE_ADMIN =>
            self::ROLE_ADMIN,
        self::ROLE_MANAGER =>
            self::ROLE_MANAGER,
        self::ROLE_ACCOUNTANT =>
            self::ROLE_ACCOUNTANT,
        self::ROLE_SALES_STAFF =>
            self::ROLE_SALES_STAFF,
    ];


    private const PAGE_RULES = [
        \App\Filament\Pages\AccountingApprovalCenter::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'permissions' => [
                self::ACCOUNTING_MANAGE,
                'account_vouchers_edit',
                'visa_payments_edit',
                'flight_payments_edit',
                'umrah_payments_edit',
                'hotel_payments_edit',
                'refunds_edit',
                'invoices_edit',
            ],
        ],
        \App\Filament\Pages\AccountingPostingAudit::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'permissions' => [
                self::ACCOUNTING_MANAGE,
                'account_vouchers_view',
                'reports_view',
            ],
        ],
        \App\Filament\Pages\BankReconciliation::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'permissions' => [
                self::ACCOUNTING_MANAGE,
                'visa_payments_edit',
                'flight_payments_edit',
                'umrah_payments_edit',
                'hotel_payments_edit',
            ],
        ],
        \App\Filament\Pages\AccountLedgerReport::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'permissions' => [
                self::ACCOUNTING_MANAGE,
                'account_vouchers_view',
                'reports_view',
            ],
        ],        \App\Filament\Pages\CustomerAgingReport::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'permissions' => [
                'reports_view',
            ],
        ],
        \App\Filament\Pages\HrReports::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'permissions' => [
                'reports_view',
                'users_view',
                'users_manage',
            ],
        ],
        \App\Filament\Pages\SupplierAgingReport::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'permissions' => [
                'reports_view',
            ],
        ],
    ];

    private const RESOURCE_RULES = [
        \App\Filament\Resources\Hotels\HotelResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'hotels_view',
                ],
                'create' => [
                    'hotels_create',
                ],
                'edit' => [
                    'hotels_edit',
                ],
                'delete' => [
                    'hotels_delete',
                ],
            ],
        ],
        \App\Filament\Resources\BankAccounts\BankAccountResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'actions' => [
                'view' => [
                    self::ACCOUNTING_MANAGE,
                    'reports_view',
                    'visa_payments_view',
                    'flight_payments_view',
                    'umrah_payments_view',
                    'hotel_payments_view',
                ],
                'create' => [
                    self::ACCOUNTING_MANAGE,
                    'visa_payments_create',
                    'flight_payments_create',
                    'umrah_payments_create',
                    'hotel_payments_create',
                ],
                'edit' => [
                    self::ACCOUNTING_MANAGE,
                    'visa_payments_edit',
                    'flight_payments_edit',
                    'umrah_payments_edit',
                    'hotel_payments_edit',
                ],
                'delete' => [
                    self::ACCOUNTING_MANAGE,
                    'visa_payments_delete',
                    'flight_payments_delete',
                    'umrah_payments_delete',
                    'hotel_payments_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Departments\DepartmentResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Designations\DesignationResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\LeaveTypes\LeaveTypeResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Staff\StaffResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffDocuments\StaffDocumentResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffLeaves\StaffLeaveResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'users_manage',
                    'users_view',
                ],
                'create' => [
                    'users_manage',
                    'users_create',
                ],
                'edit' => [
                    'users_manage',
                    'users_edit',
                ],
                'delete' => [
                    'users_manage',
                    'users_delete',
                ],
            ],
        ],
        \App\Filament\Resources\SupplierBills\SupplierBillResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'actions' => [
                'view' => [
                    self::ACCOUNTING_MANAGE,
                    'reports_view',
                    'invoices_view',
                    'visa_payments_view',
                    'flight_payments_view',
                    'umrah_payments_view',
                    'hotel_payments_view',
                ],
                'create' => [
                    self::ACCOUNTING_MANAGE,
                    'invoices_create',
                    'visa_payments_create',
                    'flight_payments_create',
                    'umrah_payments_create',
                    'hotel_payments_create',
                ],
                'edit' => [
                    self::ACCOUNTING_MANAGE,
                    'invoices_edit',
                    'visa_payments_edit',
                    'flight_payments_edit',
                    'umrah_payments_edit',
                    'hotel_payments_edit',
                ],
                'delete' => [
                    self::ACCOUNTING_MANAGE,
                    'invoices_delete',
                    'visa_payments_delete',
                    'flight_payments_delete',
                    'umrah_payments_delete',
                    'hotel_payments_delete',
                ],
            ],
        ],
        \App\Filament\Resources\SupplierPayments\SupplierPaymentResource::class =>
            [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'actions' => [
                'view' => [
                    self::ACCOUNTING_MANAGE,
                    'reports_view',
                    'invoices_view',
                    'visa_payments_view',
                    'flight_payments_view',
                    'umrah_payments_view',
                    'hotel_payments_view',
                ],
                'create' => [
                    self::ACCOUNTING_MANAGE,
                    'invoices_create',
                    'visa_payments_create',
                    'flight_payments_create',
                    'umrah_payments_create',
                    'hotel_payments_create',
                ],
                'edit' => [
                    self::ACCOUNTING_MANAGE,
                    'invoices_edit',
                    'visa_payments_edit',
                    'flight_payments_edit',
                    'umrah_payments_edit',
                    'hotel_payments_edit',
                ],
                'delete' => [
                    self::ACCOUNTING_MANAGE,
                    'invoices_delete',
                    'visa_payments_delete',
                    'flight_payments_delete',
                    'umrah_payments_delete',
                    'hotel_payments_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Suppliers\SupplierResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'suppliers_view',
                ],
                'create' => [
                    'suppliers_create',
                ],
                'edit' => [
                    'suppliers_edit',
                ],
                'delete' => [
                    'suppliers_delete',
                ],
            ],
        ],
    ];

    public static function roleOptions(): array
    {
        return self::ROLE_OPTIONS;
    }

    public static function normalizedPermission(string $permission): string
    {
        $permission =
            strtolower(
                trim(
                    $permission,
                ),
            );

        $permission =
            str_replace(
                [
                    '.',
                    '-',
                    ':',
                    ' ',
                ],
                '_',
                $permission,
            );

        return trim(
            preg_replace(
                '/_+/',
                '_',
                $permission,
            )
            ?? $permission,
            '_',
        );
    }

    public static function normalizedPermissions(mixed $permissions): array
    {
        if (! is_array($permissions)) {
            return [];
        }

        $permissions =
            array_map(
                static fn (mixed $permission): string =>
                    self::normalizedPermission(
                        (string) $permission,
                    ),
                $permissions,
            );

        $permissions =
            array_values(
                array_unique(
                    array_filter(
                        $permissions,
                        static fn (string $permission): bool =>
                            $permission !== '',
                    ),
                ),
            );

        sort($permissions);

        return $permissions;
    }

    public static function userHasPermission(
        mixed $assignedPermissions,
        string $requestedPermission,
    ): bool {
        return in_array(
            self::normalizedPermission(
                $requestedPermission,
            ),
            self::normalizedPermissions(
                $assignedPermissions,
            ),
            true,
        );
    }

    public static function userHasAnyPermission(
        mixed $assignedPermissions,
        array $requestedPermissions,
    ): bool {
        $assigned =
            self::normalizedPermissions(
                $assignedPermissions,
            );

        foreach ($requestedPermissions as $permission) {
            if (
                in_array(
                    self::normalizedPermission(
                        (string) $permission,
                    ),
                    $assigned,
                    true,
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public static function canAccessPanel(
        User $user,
        Panel $panel,
    ): bool {
        if (
            ! (bool) $user->is_active
            || $panel->getId() !== 'admin'
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $role =
            (string) $user->role;

        if (
            ! array_key_exists(
                $role,
                self::ROLE_OPTIONS,
            )
        ) {
            return false;
        }

        return self::normalizedPermissions(
            $user->permissions,
        ) !== [];
    }

    public static function allowsPage(
        ?User $user,
        string $pageClass,
    ): bool {
        if (
            ! $user
            || ! (bool) $user->is_active
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $rule =
            self::PAGE_RULES[$pageClass]
            ?? null;

        if (! is_array($rule)) {
            return false;
        }

        return self::roleIsAllowed(
            $user,
            $rule['roles']
            ?? [],
        )
            && self::userHasAnyPermission(
                $user->permissions,
                $rule['permissions']
                ?? [],
            );
    }

    public static function allowsResourceAction(
        ?User $user,
        string $resourceClass,
        string $action,
    ): bool {
        if (
            ! $user
            || ! (bool) $user->is_active
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $rule =
            self::RESOURCE_RULES[$resourceClass]
            ?? null;

        if (! is_array($rule)) {
            return false;
        }

        $permissions =
            $rule['actions'][$action]
            ?? [];

        return $permissions !== []
            && self::roleIsAllowed(
                $user,
                $rule['roles']
                ?? [],
            )
            && self::userHasAnyPermission(
                $user->permissions,
                $permissions,
            );
    }

    public static function pageClasses(): array
    {
        return array_keys(
            self::PAGE_RULES,
        );
    }

    public static function resourceClasses(): array
    {
        return array_keys(
            self::RESOURCE_RULES,
        );
    }

    public static function rolePresetAdditions(?string $role): array
    {
        return match ($role) {
            self::ROLE_MANAGER,
            self::ROLE_ACCOUNTANT => [
                self::ACCOUNTING_MANAGE,
            ],
            default => [],
        };
    }

    private static function roleIsAllowed(
        User $user,
        array $roles,
    ): bool {
        return in_array(
            (string) $user->role,
            $roles,
            true,
        );
    }

}
