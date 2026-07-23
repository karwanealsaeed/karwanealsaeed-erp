<?php

declare(strict_types=1);

namespace App\Support;

use App\Filament\Resources\Users\Schemas\UserForm;

final class ProposalRoleMapping
{
    public const SUPER_ADMIN = 'Super Admin';

    public const DIRECTOR = 'Director';

    public const ACCOUNTANT = 'Accountant';

    public const TICKETING_MANAGER = 'Ticketing Manager';

    public const VISA_MANAGER = 'Visa Manager';

    public const UMRAH_MANAGER = 'Umrah Manager';

    public const HR_MANAGER = 'HR Manager';

    public const MARKETING_MANAGER = 'Marketing Manager';

    public const CUSTOMER_SUPPORT = 'Customer Support';

    public const CUSTOMER = 'Customer';

    /**
     * @return list<string>
     */
    public static function labels(): array
    {
        return [
            self::SUPER_ADMIN,
            self::DIRECTOR,
            self::ACCOUNTANT,
            self::TICKETING_MANAGER,
            self::VISA_MANAGER,
            self::UMRAH_MANAGER,
            self::HR_MANAGER,
            self::MARKETING_MANAGER,
            self::CUSTOMER_SUPPORT,
            self::CUSTOMER,
        ];
    }

    /**
     * Specialized mappings never inherit the broad Manager preset.
     *
     * @return array<string, array{
     *     base_role: string|null,
     *     identity: string,
     *     permission_mode: string,
     *     permissions: list<string>,
     *     description: string
     * }>
     */
    public static function mappings(): array
    {
        $mappings = [
            self::SUPER_ADMIN => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_ADMIN,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'admin_bypass',
                'permissions' =>
                    [],
                'description' =>
                    'Full administrative bypass. Restricted to the principal system administrator.',
            ],
            self::DIRECTOR => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_MANAGER,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::directorPermissions(),
                'description' =>
                    'Read-only executive oversight with controlled report export access and no user, system, restore or mutation rights.',
            ],
            self::ACCOUNTANT => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'base_default',
                'permissions' =>
                    UserForm::getDefaultPermissions(
                        RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    ),
                'description' =>
                    'Existing tested Accountant default permission contract.',
            ],
            self::TICKETING_MANAGER => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_MANAGER,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::ticketingManagerPermissions(),
                'description' =>
                    'Ticketing, flight, customer-document and operational reporting permissions without delete rights.',
            ],
            self::VISA_MANAGER => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_MANAGER,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::visaManagerPermissions(),
                'description' =>
                    'Visa, passport, payment and customer-document operations without delete rights.',
            ],
            self::UMRAH_MANAGER => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_MANAGER,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::umrahManagerPermissions(),
                'description' =>
                    'Umrah booking, masters, vouchers, payments and controlled reference-data access without delete rights.',
            ],
            self::HR_MANAGER => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_MANAGER,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::hrManagerPermissions(),
                'description' =>
                    'All 45 dedicated HR permissions plus reports_view for the HR Reports dashboard.',
            ],
            self::MARKETING_MANAGER => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_MANAGER,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::marketingManagerPermissions(),
                'description' =>
                    'Full website-content lifecycle permissions, including explicit content deletion.',
            ],
            self::CUSTOMER_SUPPORT => [
                'base_role' =>
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                'identity' =>
                    'staff',
                'permission_mode' =>
                    'explicit',
                'permissions' =>
                    self::customerSupportPermissions(),
                'description' =>
                    'Customer, document, inquiry, ticket and task operations without financial, executive or delete rights.',
            ],
            self::CUSTOMER => [
                'base_role' =>
                    null,
                'identity' =>
                    'customer_portal',
                'permission_mode' =>
                    'portal_identity',
                'permissions' =>
                    [],
                'description' =>
                    'Separate customer/agent portal identity. It is never assigned a staff base role.',
            ],
        ];

        foreach ($mappings as &$mapping) {
            $mapping['permissions'] =
                array_values(
                    array_unique(
                        $mapping['permissions'],
                    ),
                );
        }

        unset($mapping);

        return $mappings;
    }

    /**
     * @return array{
     *     base_role: string|null,
     *     identity: string,
     *     permission_mode: string,
     *     permissions: list<string>,
     *     description: string
     * }|null
     */
    public static function mapping(string $label): ?array
    {
        return self::mappings()[$label]
            ?? null;
    }

    /**
     * @return list<string>
     */
    public static function permissions(string $label): array
    {
        return self::mapping($label)['permissions']
            ?? [];
    }

    /**
     * @return list<string>
     */
    private static function directorPermissions(): array
    {
        return [
            'agents_view',
            'customer_documents_view',
            'customer_documents_download',
            'customers_view',
            'expenses_view',
            'expense_categories_view',
            'flight_bookings_view',
            'flight_payments_view',
            'hotel_bookings_view',
            'hotel_payments_view',
            'hotels_view',
            'invoices_view',
            'refunds_view',
            'reports_excel',
            'reports_pdf',
            'reports_print',
            'reports_view',
            'suppliers_view',
            'tasks_view',
            'insurance_providers_view',
            'travel_insurance_payments_view',
            'travel_insurance_policies_view',
            'travel_insurance_documents_download',
            'travel_insurance_receipts_download',
            'umrah_bookings_view',
            'umrah_payments_view',
            'visa_applications_view',
            'visa_payments_view',
            'support_inquiries_view',
            'support_tickets_view',
        ];
    }

    /**
     * @return list<string>
     */
    private static function ticketingManagerPermissions(): array
    {
        return [
            'countries_view',
            'countries_create',
            'countries_edit',
            'airports_view',
            'airports_create',
            'airports_edit',
            'cities_view',
            'cities_create',
            'cities_edit',
            'airlines_view',
            'airlines_create',
            'airlines_edit',
            'customers_view',
            'customers_create',
            'customers_edit',
            'agents_view',
            'agents_create',
            'agents_edit',
            'customer_documents_view',
            'customer_documents_create',
            'customer_documents_edit',
            'customer_documents_download',
            'flight_bookings_view',
            'flight_bookings_create',
            'flight_bookings_edit',
            'flight_payments_view',
            'flight_payments_create',
            'flight_payments_edit',
            'flight_payments_print',
            'tasks_view',
            'tasks_create',
            'tasks_edit',
            'reports_view',
            'reports_print',
            'reports_pdf',
        ];
    }

    /**
     * @return list<string>
     */
    private static function visaManagerPermissions(): array
    {
        return [
            'countries_view',
            'countries_create',
            'countries_edit',
            'visa_types_view',
            'visa_types_create',
            'visa_types_edit',
            'customers_view',
            'customers_create',
            'customers_edit',
            'customer_documents_view',
            'customer_documents_create',
            'customer_documents_edit',
            'customer_documents_download',
            'visa_applications_view',
            'visa_applications_create',
            'visa_applications_edit',
            'visa_payments_view',
            'visa_payments_create',
            'visa_payments_edit',
            'visa_payments_print',
            'tasks_view',
            'tasks_create',
            'tasks_edit',
        ];
    }

    /**
     * @return list<string>
     */
    private static function umrahManagerPermissions(): array
    {
        return [
            'customers_view',
            'customers_create',
            'customers_edit',
            'customer_documents_view',
            'customer_documents_create',
            'customer_documents_edit',
            'customer_documents_download',
            'umrah_bookings_view',
            'umrah_bookings_create',
            'umrah_bookings_edit',
            'umrah_group_flights_view',
            'umrah_group_flights_create',
            'umrah_group_flights_edit',
            'umrah_hotels_view',
            'umrah_hotels_create',
            'umrah_hotels_edit',
            'umrah_packages_view',
            'umrah_packages_create',
            'umrah_packages_edit',
            'umrah_transport_routes_view',
            'umrah_transport_routes_create',
            'umrah_transport_routes_edit',
            'umrah_vouchers_view',
            'umrah_vouchers_create',
            'umrah_vouchers_edit',
            'umrah_vouchers_print',
            'umrah_payments_view',
            'umrah_payments_create',
            'umrah_payments_edit',
            'umrah_payments_print',
            'countries_view',
            'airports_view',
            'cities_view',
            'airlines_view',
            'hotels_view',
            'tasks_view',
            'tasks_create',
            'tasks_edit',
            'reports_view',
        ];
    }

    /**
     * @return list<string>
     */
    private static function hrManagerPermissions(): array
    {
        $permissions =
            array_keys(
                UserForm::getPermissionGroups()[
                    'permissions_hr'
                ]['options']
                ?? [],
            );

        $permissions[] =
            'reports_view';

        return $permissions;
    }

    /**
     * @return list<string>
     */
    private static function marketingManagerPermissions(): array
    {
        return [
            'pakistan_tours_view',
            'pakistan_tours_create',
            'pakistan_tours_edit',
            'pakistan_tours_delete',
            'gallery_view',
            'gallery_create',
            'gallery_edit',
            'gallery_delete',
            'testimonials_view',
            'testimonials_create',
            'testimonials_edit',
            'testimonials_delete',
            'faqs_view',
            'faqs_create',
            'faqs_edit',
            'faqs_delete',
            'blog_posts_view',
            'blog_posts_create',
            'blog_posts_edit',
            'blog_posts_delete',
            'promotions_view',
            'promotions_create',
            'promotions_edit',
            'promotions_delete',
        ];
    }

    /**
     * @return list<string>
     */
    private static function customerSupportPermissions(): array
    {
        return [
            'customers_view',
            'customers_create',
            'customers_edit',
            'agents_view',
            'customer_documents_view',
            'customer_documents_download',
            'support_inquiries_view',
            'support_inquiries_edit',
            'support_tickets_view',
            'support_tickets_create',
            'support_tickets_edit',
            'tasks_view',
            'tasks_create',
            'tasks_edit',
        ];
    }
}