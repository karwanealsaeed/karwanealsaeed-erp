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
                'staff_view',
                'reports_view',
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
        \App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'actions' => [
                'view' => [
                    self::ACCOUNTING_MANAGE,
                    'account_vouchers_view',
                    'reports_view',
                    'visa_payments_view',
                    'flight_payments_view',
                    'umrah_payments_view',
                    'hotel_payments_view',
                ],
                'create' => [
                    self::ACCOUNTING_MANAGE,
                ],
                'edit' => [
                    self::ACCOUNTING_MANAGE,
                ],
                'delete' => [
                    self::ACCOUNTING_MANAGE,
                ],
            ],
        ],
        \App\Filament\Resources\BankStatementEntries\BankStatementEntryResource::class => [
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
        \App\Filament\Resources\AccountHeads\AccountHeadResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
            ],
            'actions' => [
                'view' => [
                    self::ACCOUNTING_MANAGE,
                    'reports_view',
                ],
                'create' => [
                    self::ACCOUNTING_MANAGE,
                ],
                'edit' => [
                    self::ACCOUNTING_MANAGE,
                ],
                'delete' => [
                    self::ACCOUNTING_MANAGE,
                ],
            ],
        ],
        \App\Filament\Resources\Airlines\AirlineResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'airlines_view',
                ],
                'create' => [
                    'airlines_create',
                ],
                'edit' => [
                    'airlines_edit',
                ],
                'delete' => [
                    'airlines_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Airports\AirportResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'airports_view',
                ],
                'create' => [
                    'airports_create',
                ],
                'edit' => [
                    'airports_edit',
                ],
                'delete' => [
                    'airports_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Cities\CityResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'cities_view',
                ],
                'create' => [
                    'cities_create',
                ],
                'edit' => [
                    'cities_edit',
                ],
                'delete' => [
                    'cities_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Countries\CountryResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'countries_view',
                ],
                'create' => [
                    'countries_create',
                ],
                'edit' => [
                    'countries_edit',
                ],
                'delete' => [
                    'countries_delete',
                ],
            ],
        ],
        \App\Filament\Resources\FlightBookings\FlightBookingResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'flight_bookings_view',
                ],
                'create' => [
                    'flight_bookings_create',
                ],
                'edit' => [
                    'flight_bookings_edit',
                ],
                'delete' => [
                    'flight_bookings_delete',
                ],
            ],
        ],
        \App\Filament\Resources\FlightPayments\FlightPaymentResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'flight_payments_view',
                ],
                'create' => [
                    'flight_payments_create',
                ],
                'edit' => [
                    'flight_payments_edit',
                ],
                'delete' => [
                    'flight_payments_delete',
                ],
            ],
        ],
        \App\Filament\Resources\VisaApplications\VisaApplicationResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'visa_applications_view',
                ],
                'create' => [
                    'visa_applications_create',
                ],
                'edit' => [
                    'visa_applications_edit',
                ],
                'delete' => [
                    'visa_applications_delete',
                ],
            ],
        ],
        \App\Filament\Resources\VisaTypes\VisaTypeResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'visa_types_view',
                ],
                'create' => [
                    'visa_types_create',
                ],
                'edit' => [
                    'visa_types_edit',
                ],
                'delete' => [
                    'visa_types_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahBookings\UmrahBookingResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_bookings_view',
                ],
                'create' => [
                    'umrah_bookings_create',
                ],
                'edit' => [
                    'umrah_bookings_edit',
                ],
                'delete' => [
                    'umrah_bookings_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahPayments\UmrahPaymentResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_payments_view',
                ],
                'create' => [
                    'umrah_payments_create',
                ],
                'edit' => [
                    'umrah_payments_edit',
                ],
                'delete' => [
                    'umrah_payments_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahGroupFlights\UmrahGroupFlightResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_group_flights_view',
                ],
                'create' => [
                    'umrah_group_flights_create',
                ],
                'edit' => [
                    'umrah_group_flights_edit',
                ],
                'delete' => [
                    'umrah_group_flights_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahHotels\UmrahHotelResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_hotels_view',
                ],
                'create' => [
                    'umrah_hotels_create',
                ],
                'edit' => [
                    'umrah_hotels_edit',
                ],
                'delete' => [
                    'umrah_hotels_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahPackages\UmrahPackageResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_packages_view',
                ],
                'create' => [
                    'umrah_packages_create',
                ],
                'edit' => [
                    'umrah_packages_edit',
                ],
                'delete' => [
                    'umrah_packages_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahTransportRoutes\UmrahTransportRouteResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_transport_routes_view',
                ],
                'create' => [
                    'umrah_transport_routes_create',
                ],
                'edit' => [
                    'umrah_transport_routes_edit',
                ],
                'delete' => [
                    'umrah_transport_routes_delete',
                ],
            ],
        ],
        \App\Filament\Resources\UmrahVouchers\UmrahVoucherResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'umrah_vouchers_view',
                ],
                'create' => [
                    'umrah_vouchers_create',
                ],
                'edit' => [
                    'umrah_vouchers_edit',
                ],
                'delete' => [
                    'umrah_vouchers_delete',
                ],
            ],
        ],
        \App\Filament\Resources\SupportInquiries\SupportInquiryResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'support_inquiries_view',
                ],
                'create' => [
                    'support_inquiries_create',
                ],
                'edit' => [
                    'support_inquiries_edit',
                ],
                'delete' => [
                    'support_inquiries_delete',
                ],
            ],
        ],
        \App\Filament\Resources\SupportTickets\SupportTicketResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
                self::ROLE_ACCOUNTANT,
                self::ROLE_SALES_STAFF,
            ],
            'actions' => [
                'view' => [
                    'support_tickets_view',
                ],
                'create' => [
                    'support_tickets_create',
                ],
                'edit' => [
                    'support_tickets_edit',
                ],
                'delete' => [
                    'support_tickets_delete',
                ],
            ],
        ],
        \App\Filament\Resources\BlogPosts\BlogPostResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'blog_posts_view',
                ],
                'create' => [
                    'blog_posts_create',
                ],
                'edit' => [
                    'blog_posts_edit',
                ],
                'delete' => [
                    'blog_posts_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Faqs\FaqResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'faqs_view',
                ],
                'create' => [
                    'faqs_create',
                ],
                'edit' => [
                    'faqs_edit',
                ],
                'delete' => [
                    'faqs_delete',
                ],
            ],
        ],
        \App\Filament\Resources\GalleryItems\GalleryItemResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'gallery_view',
                ],
                'create' => [
                    'gallery_create',
                ],
                'edit' => [
                    'gallery_edit',
                ],
                'delete' => [
                    'gallery_delete',
                ],
            ],
        ],
        \App\Filament\Resources\PakistanTours\PakistanTourResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'pakistan_tours_view',
                ],
                'create' => [
                    'pakistan_tours_create',
                ],
                'edit' => [
                    'pakistan_tours_edit',
                ],
                'delete' => [
                    'pakistan_tours_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Promotions\PromotionResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'promotions_view',
                ],
                'create' => [
                    'promotions_create',
                ],
                'edit' => [
                    'promotions_edit',
                ],
                'delete' => [
                    'promotions_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Testimonials\TestimonialResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'testimonials_view',
                ],
                'create' => [
                    'testimonials_create',
                ],
                'edit' => [
                    'testimonials_edit',
                ],
                'delete' => [
                    'testimonials_delete',
                ],
            ],
        ],
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
        \App\Filament\Resources\Departments\DepartmentResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'departments_view',
                ],
                'create' => [
                    'departments_create',
                ],
                'edit' => [
                    'departments_edit',
                ],
                'delete' => [
                    'departments_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Designations\DesignationResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'designations_view',
                ],
                'create' => [
                    'designations_create',
                ],
                'edit' => [
                    'designations_edit',
                ],
                'delete' => [
                    'designations_delete',
                ],
            ],
        ],
        \App\Filament\Resources\LeaveTypes\LeaveTypeResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'leave_types_view',
                ],
                'create' => [
                    'leave_types_create',
                ],
                'edit' => [
                    'leave_types_edit',
                ],
                'delete' => [
                    'leave_types_delete',
                ],
            ],
        ],
        \App\Filament\Resources\Staff\StaffResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_view',
                ],
                'create' => [
                    'staff_create',
                ],
                'edit' => [
                    'staff_edit',
                ],
                'delete' => [
                    'staff_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffAttendances\StaffAttendanceResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_attendances_view',
                ],
                'create' => [
                    'staff_attendances_create',
                ],
                'edit' => [
                    'staff_attendances_edit',
                ],
                'delete' => [
                    'staff_attendances_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffDocuments\StaffDocumentResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_documents_view',
                ],
                'create' => [
                    'staff_documents_create',
                ],
                'edit' => [
                    'staff_documents_edit',
                ],
                'delete' => [
                    'staff_documents_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffLeaves\StaffLeaveResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_leaves_view',
                ],
                'create' => [
                    'staff_leaves_create',
                ],
                'edit' => [
                    'staff_leaves_edit',
                ],
                'delete' => [
                    'staff_leaves_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_ledger_entries_view',
                ],
                'create' => [
                    'staff_ledger_entries_create',
                ],
                'edit' => [
                    'staff_ledger_entries_edit',
                ],
                'delete' => [
                    'staff_ledger_entries_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffMembers\StaffMemberResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_members_view',
                ],
                'create' => [
                    'staff_members_create',
                ],
                'edit' => [
                    'staff_members_edit',
                ],
                'delete' => [
                    'staff_members_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffPayrolls\StaffPayrollResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_payrolls_view',
                ],
                'create' => [
                    'staff_payrolls_create',
                ],
                'edit' => [
                    'staff_payrolls_edit',
                ],
                'delete' => [
                    'staff_payrolls_delete',
                ],
            ],
        ],
        \App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource::class => [
            'roles' => [
                self::ROLE_MANAGER,
            ],
            'actions' => [
                'view' => [
                    'staff_performance_notes_view',
                ],
                'create' => [
                    'staff_performance_notes_create',
                ],
                'edit' => [
                    'staff_performance_notes_edit',
                ],
                'delete' => [
                    'staff_performance_notes_delete',
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
