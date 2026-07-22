<?php

namespace App\Filament\Resources\Users\Schemas;


use App\Support\RbacPermissionRegistry;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->afterStateHydrated(function (TextInput $component): void {
                                $component->state(null);
                            })
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Edit ke time blank chorne se old password same rahega.'),

                        Select::make('role')
                            ->label('User Type / Role')
                            ->options(
                                RbacPermissionRegistry::roleOptions(),
                            )
                            ->default('Sales Staff')
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $defaultPermissions = self::getDefaultPermissions($state);

                                foreach (array_keys(self::getPermissionGroups()) as $field) {
                                    $set($field, self::getPermissionsForGroup($defaultPermissions, $field));
                                }
                            })
                            ->required()
                            ->helperText('Role select karte hi default rights auto select ho jayenge. Neeche aap rights manually change bhi kar sakte hain.'),

                        Toggle::make('is_active')
                            ->label('Active User')
                            ->default(true),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->tel()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Settings Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_settings'),
                    ])
                    ->collapsible(),

                Section::make('Master Data Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_master_data'),
                    ])
                    ->collapsible(),

                Section::make('Website Content Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_website_content'),
                    ])
                    ->collapsible(),

                Section::make('Task & Follow-up Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_tasks'),
                    ])
                    ->collapsible(),
                Section::make('Customer Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_customers'),
                    ])
                    ->collapsible(),

                Section::make('Booking Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_bookings'),
                    ])
                    ->collapsible(),

                Section::make('Finance Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_finance'),
                    ])
                    ->collapsible(),

                Section::make('Print Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_print'),
                    ])
                    ->collapsible(),

                Section::make('Reports Rights')
                    ->schema([
                        self::makePermissionCheckbox('permissions_reports'),
                    ])
                    ->collapsible(),
            ]);
    }

    private static function makePermissionCheckbox(string $field): CheckboxList
    {
        return CheckboxList::make($field)
            ->label(false)
            ->options(self::getPermissionGroups()[$field]['options'])
            ->default(fn (): array => self::getPermissionsForGroup(self::getDefaultPermissions('Sales Staff'), $field))
            ->afterStateHydrated(function (CheckboxList $component, mixed $state, ?User $record = null) use ($field): void {
                if ($record && $record->exists) {
                    $component->state(self::getPermissionsForGroup($record->permissions ?? [], $field));

                    return;
                }

                $component->state(self::getPermissionsForGroup($state ?? self::getDefaultPermissions('Sales Staff'), $field));
            })
            ->dehydrateStateUsing(fn (mixed $state): array => self::normalizePermissions($state))
            ->columns(2);
    }

    public static function getPermissionGroups(): array
    {
        return [
            'permissions_settings' => [
                'options' => [
                    'users_manage' => 'Users & Roles - Manage',
                    'settings_manage' => 'Company Settings - Manage',

                    'ai_chat_messages_view' => 'Karwan AI Chat Logs - View',
                    'ai_chat_messages_delete' => 'Karwan AI Chat Logs - Delete',

                    'ai_knowledge_bases_view' => 'Karwan AI Knowledge Base - View',
                    'ai_knowledge_bases_create' => 'Karwan AI Knowledge Base - Create',
                    'ai_knowledge_bases_edit' => 'Karwan AI Knowledge Base - Edit',
                    'ai_knowledge_bases_delete' => 'Karwan AI Knowledge Base - Delete',


                    'ai_settings_view' => 'Karwan AI Settings - View',
                    'ai_settings_edit' => 'Karwan AI Settings - Edit',
                    'ai_usage_logs_view' => 'Karwan AI Usage Logs - View',
                    'activity_logs_view' => 'Activity Logs - View',
                    'database_backups_view' => 'Database Backups - View',
                    'database_backups_create' => 'Database Backups - Create',
                    'database_backups_restore' => 'Database Backups - Restore',
                    'database_backups_download' => 'Database Backups - Download',
                    'database_backups_delete' => 'Database Backups - Delete',
                ],
            ],

            'permissions_master_data' => [
                'options' => [
                    'countries_view' => 'Countries - View',
                    'countries_create' => 'Countries - Create',
                    'countries_edit' => 'Countries - Edit',
                    'countries_delete' => 'Countries - Delete',

                    'airports_view' => 'Airports - View',
                    'airports_create' => 'Airports - Create',
                    'airports_edit' => 'Airports - Edit',
                    'airports_delete' => 'Airports - Delete',

                    'airlines_view' => 'Airlines - View',
                    'airlines_create' => 'Airlines - Create',
                    'airlines_edit' => 'Airlines - Edit',
                    'airlines_delete' => 'Airlines - Delete',

                    'visa_types_view' => 'Visa Types - View',
                    'visa_types_create' => 'Visa Types - Create',
                    'visa_types_edit' => 'Visa Types - Edit',
                    'visa_types_delete' => 'Visa Types - Delete',

                    'suppliers_view' => 'Suppliers - View',
                    'suppliers_create' => 'Suppliers - Create',
                    'suppliers_edit' => 'Suppliers - Edit',
                    'suppliers_delete' => 'Suppliers - Delete',

                    'insurance_providers_view' => 'Insurance Providers - View',
                    'insurance_providers_create' => 'Insurance Providers - Create',
                    'insurance_providers_edit' => 'Insurance Providers - Edit',
                    'insurance_providers_delete' => 'Insurance Providers - Delete',

                    'travel_insurance_policies_view' => 'Travel Insurance Policies - View',
                    'travel_insurance_policies_create' => 'Travel Insurance Policies - Create',
                    'travel_insurance_policies_edit' => 'Travel Insurance Policies - Edit',
                    'travel_insurance_policies_delete' => 'Travel Insurance Policies - Delete',

                    'travel_insurance_payments_view' => 'Travel Insurance Payments - View',
                    'travel_insurance_payments_create' => 'Travel Insurance Payments - Create',
                    'travel_insurance_payments_edit' => 'Travel Insurance Payments - Edit',
                    'travel_insurance_payments_delete' => 'Travel Insurance Payments - Delete',
                    'travel_insurance_payments_approve' => 'Travel Insurance Payments - Approve / Reject',

                    'travel_insurance_documents_download' => 'Travel Insurance - Download Policy Documents',
                    'travel_insurance_receipts_download' => 'Travel Insurance - Download Payment Receipts',

                    'travel_insurance_policies_invoice' => 'Travel Insurance Policies - Create / Sync Invoice',
                ],
            ],

            'permissions_website_content' => [
                'options' => [
                    'pakistan_tours_view' => 'Pakistan Tours - View',
                    'pakistan_tours_create' => 'Pakistan Tours - Create',
                    'pakistan_tours_edit' => 'Pakistan Tours - Edit',
                    'pakistan_tours_delete' => 'Pakistan Tours - Delete',

                    'gallery_view' => 'Gallery - View',
                    'gallery_create' => 'Gallery - Create',
                    'gallery_edit' => 'Gallery - Edit',
                    'gallery_delete' => 'Gallery - Delete',

                    'testimonials_view' => 'Testimonials - View',
                    'testimonials_create' => 'Testimonials - Create',
                    'testimonials_edit' => 'Testimonials - Edit',
                    'testimonials_delete' => 'Testimonials - Delete',

                    'faqs_view' => 'FAQs - View',
                    'faqs_create' => 'FAQs - Create',
                    'faqs_edit' => 'FAQs - Edit',
                    'faqs_delete' => 'FAQs - Delete',

                    'blog_posts_view' => 'Blog / News - View',
                    'blog_posts_create' => 'Blog / News - Create',
                    'blog_posts_edit' => 'Blog / News - Edit',
                    'blog_posts_delete' => 'Blog / News - Delete',

                    'promotions_view' => 'Promotions - View',
                    'promotions_create' => 'Promotions - Create',
                    'promotions_edit' => 'Promotions - Edit',
                    'promotions_delete' => 'Promotions - Delete',
                ],
            ],

            'permissions_tasks' => [
                'options' => [
                    'tasks_view' => 'Tasks & Follow-ups - View',
                    'tasks_create' => 'Tasks & Follow-ups - Create',
                    'tasks_edit' => 'Tasks & Follow-ups - Edit',
                    'tasks_delete' => 'Tasks & Follow-ups - Delete',
                ],
            ],
            'permissions_customers' => [
                'options' => [
                    'customers_view' => 'Customers - View',
                    'customers_create' => 'Customers - Create',
                    'customers_edit' => 'Customers - Edit',
                    'customers_delete' => 'Customers - Delete',

                    'customer_documents_view' => 'Customer Documents - View',
                    'customer_documents_create' => 'Customer Documents - Create',
                    'customer_documents_edit' => 'Customer Documents - Edit',
                    'customer_documents_delete' => 'Customer Documents - Delete',
                    'customer_documents_download' => 'Customer Documents - Download',
                ],
            ],

            'permissions_bookings' => [
                'options' => [
                    'visa_applications_view' => 'Visa Applications - View',
                    'visa_applications_create' => 'Visa Applications - Create',
                    'visa_applications_edit' => 'Visa Applications - Edit',
                    'visa_applications_delete' => 'Visa Applications - Delete',

                    'flight_bookings_view' => 'Flight Bookings - View',
                    'flight_bookings_create' => 'Flight Bookings - Create',
                    'flight_bookings_edit' => 'Flight Bookings - Edit',
                    'flight_bookings_delete' => 'Flight Bookings - Delete',

                    'umrah_bookings_view' => 'Umrah Bookings - View',
                    'umrah_bookings_create' => 'Umrah Bookings - Create',
                    'umrah_bookings_edit' => 'Umrah Bookings - Edit',
                    'umrah_bookings_delete' => 'Umrah Bookings - Delete',

                    'hotel_bookings_view' => 'Hotel Bookings - View',
                    'hotel_bookings_create' => 'Hotel Bookings - Create',
                    'hotel_bookings_edit' => 'Hotel Bookings - Edit',
                    'hotel_bookings_delete' => 'Hotel Bookings - Delete',
                ],
            ],

            'permissions_finance' => [
                'options' => [
                    RbacPermissionRegistry::ACCOUNTING_MANAGE => 'Accounting Control Center - Manage',

                    'visa_payments_view' => 'Visa Payments - View',
                    'visa_payments_create' => 'Visa Payments - Create',
                    'visa_payments_edit' => 'Visa Payments - Edit',
                    'visa_payments_delete' => 'Visa Payments - Delete',

                    'flight_payments_view' => 'Flight Payments - View',
                    'flight_payments_create' => 'Flight Payments - Create',
                    'flight_payments_edit' => 'Flight Payments - Edit',
                    'flight_payments_delete' => 'Flight Payments - Delete',

                    'umrah_payments_view' => 'Umrah Payments - View',
                    'umrah_payments_create' => 'Umrah Payments - Create',
                    'umrah_payments_edit' => 'Umrah Payments - Edit',
                    'umrah_payments_delete' => 'Umrah Payments - Delete',

                    'hotel_payments_view' => 'Hotel Payments - View',
                    'hotel_payments_create' => 'Hotel Payments - Create',
                    'hotel_payments_edit' => 'Hotel Payments - Edit',
                    'hotel_payments_delete' => 'Hotel Payments - Delete',

                    'expenses_view' => 'Expenses - View',
                    'expenses_create' => 'Expenses - Create',
                    'expenses_edit' => 'Expenses - Edit',
                    'expenses_delete' => 'Expenses - Delete',

                    'expense_categories_view' => 'Expense Categories - View',
                    'expense_categories_create' => 'Expense Categories - Create',
                    'expense_categories_edit' => 'Expense Categories - Edit',
                    'expense_categories_delete' => 'Expense Categories - Delete',

                    'refunds_view' => 'Refunds - View',
                    'refunds_create' => 'Refunds - Create',
                    'refunds_edit' => 'Refunds - Edit',
                    'refunds_delete' => 'Refunds - Delete',

                    'invoices_view' => 'Invoices - View',
                    'invoices_create' => 'Invoices - Create',
                    'invoices_edit' => 'Invoices - Edit',
                    'invoices_delete' => 'Invoices - Delete',
                ],
            ],

            'permissions_print' => [
                'options' => [
                    'visa_payments_print' => 'Visa Payment Receipt - Print',
                    'flight_payments_print' => 'Flight Payment Receipt - Print',
                    'umrah_payments_print' => 'Umrah Payment Receipt - Print',
                    'hotel_payments_print' => 'Hotel Payment Receipt - Print',
                    'refunds_print' => 'Refund Voucher - Print',
                    'invoices_print' => 'Invoice - Print',
                ],
            ],

            'permissions_reports' => [
                'options' => [
                    'reports_view' => 'Reports - View',
                    'reports_print' => 'Reports - Print',
                    'reports_pdf' => 'Reports - Download PDF',
                    'reports_excel' => 'Reports - Download Excel',
                ],
            ],
        ];
    }

    public static function getPermissionOptions(): array
    {
        $options = [];

        foreach (self::getPermissionGroups() as $group) {
            $options = array_merge($options, $group['options']);
        }

        return $options;
    }

    public static function getDefaultPermissions(?string $role): array
    {
        return match ($role) {
            'Admin' => array_keys(self::getPermissionOptions()),

            'Manager' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
                'users_manage',
                'settings_manage',

                'ai_chat_messages_view',

                'ai_knowledge_bases_view',
                'ai_knowledge_bases_create',
                'ai_knowledge_bases_edit',


                'ai_settings_view',
                'ai_usage_logs_view',
                'activity_logs_view',

                'countries_view',
                'airports_view',
                'airlines_view',
                'visa_types_view',
                'suppliers_view',

                'pakistan_tours_view',
                'pakistan_tours_create',
                'pakistan_tours_edit',
                'gallery_view',
                'gallery_create',
                'gallery_edit',

                'testimonials_view',
                'testimonials_create',
                'testimonials_edit',

                'faqs_view',
                'faqs_create',
                'faqs_edit',

                'blog_posts_view',
                'blog_posts_create',
                'blog_posts_edit',

                'promotions_view',
                'promotions_create',
                'promotions_edit',
                'promotions_delete',

                'tasks_view',
                'tasks_create',
                'tasks_edit',
                'tasks_delete',
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

                'flight_bookings_view',
                'flight_bookings_create',
                'flight_bookings_edit',

                'umrah_bookings_view',
                'umrah_bookings_create',
                'umrah_bookings_edit',

                'hotel_bookings_view',
                'hotel_bookings_create',
                'hotel_bookings_edit',

                'visa_payments_view',
                'visa_payments_create',
                'visa_payments_edit',

                'flight_payments_view',
                'flight_payments_create',
                'flight_payments_edit',

                'umrah_payments_view',
                'umrah_payments_create',
                'umrah_payments_edit',

                'hotel_payments_view',
                'hotel_payments_create',
                'hotel_payments_edit',

                'expenses_view',
                'expenses_create',
                'expenses_edit',

                'expense_categories_view',
                'expense_categories_create',
                'expense_categories_edit',

                'refunds_view',
                'refunds_create',
                'refunds_edit',

                'invoices_view',
                'invoices_create',
                'invoices_edit',

                'visa_payments_print',
                'flight_payments_print',
                'umrah_payments_print',
                'hotel_payments_print',
                'refunds_print',
                'invoices_print',

                'reports_view',
                'reports_print',
                'reports_pdf',
                'reports_excel',
            ],

            'Accountant' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
                'customers_view',
                'customer_documents_view',
                'customer_documents_download',

                'visa_applications_view',
                'flight_bookings_view',
                'umrah_bookings_view',
                'hotel_bookings_view',

                'visa_payments_view',
                'visa_payments_create',
                'visa_payments_edit',

                'flight_payments_view',
                'flight_payments_create',
                'flight_payments_edit',

                'umrah_payments_view',
                'umrah_payments_create',
                'umrah_payments_edit',

                'hotel_payments_view',
                'hotel_payments_create',
                'hotel_payments_edit',

                'expenses_view',
                'expenses_create',
                'expenses_edit',

                'expense_categories_view',

                'refunds_view',
                'refunds_create',
                'refunds_edit',

                'invoices_view',
                'invoices_create',
                'invoices_edit',

                'visa_payments_print',
                'flight_payments_print',
                'umrah_payments_print',
                'hotel_payments_print',
                'refunds_print',
                'invoices_print',

                'reports_view',
                'reports_print',
                'reports_pdf',
                'reports_excel',
            ],

            'Sales Staff' => [
                'countries_view',
                'airports_view',
                'airlines_view',
                'visa_types_view',
                'suppliers_view',

                'tasks_view',
                'tasks_create',
                'tasks_edit',
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

                'flight_bookings_view',
                'flight_bookings_create',
                'flight_bookings_edit',

                'umrah_bookings_view',
                'umrah_bookings_create',
                'umrah_bookings_edit',

                'hotel_bookings_view',
                'hotel_bookings_create',
                'hotel_bookings_edit',

                'visa_payments_view',
                'visa_payments_create',

                'flight_payments_view',
                'flight_payments_create',

                'umrah_payments_view',
                'umrah_payments_create',

                'hotel_payments_view',
                'hotel_payments_create',

                'invoices_view',
                'invoices_create',

                'visa_payments_print',
                'flight_payments_print',
                'umrah_payments_print',
                'hotel_payments_print',
                'invoices_print',
            ],

            default => [],
        };
    }

    public static function getPermissionsForGroup(mixed $permissions, string $field): array
    {
        $permissions = self::normalizePermissions($permissions);
        $groupPermissions = array_keys(self::getPermissionGroups()[$field]['options'] ?? []);

        return array_values(array_intersect($permissions, $groupPermissions));
    }

    public static function normalizePermissions(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $validPermissions = array_keys(self::getPermissionOptions());

        $state = array_map(function ($permission): string {
            return str_replace('.', '_', (string) $permission);
        }, $state);

        return array_values(array_unique(array_intersect($state, $validPermissions)));
    }

    public static function prepareDataForSave(array $data): array
    {
        $permissions = [];

        foreach (array_keys(self::getPermissionGroups()) as $field) {
            $permissions = array_merge($permissions, $data[$field] ?? []);

            unset($data[$field]);
        }

        $data['permissions'] = self::normalizePermissions($permissions);

        return $data;
    }
}
