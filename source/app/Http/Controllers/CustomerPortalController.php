<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\CustomerFamilyMember;
use App\Models\CustomerNotification;
use App\Models\CustomerNotificationRead;
use App\Models\CustomerPaymentUpload;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\UnifiedCustomerStatementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerPortalController extends Controller
{
    public function login(): View
    {
        return view('customer-portal.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $login = trim($data['login']);

        $customer = Customer::query()
            ->where('mobile', $login)
            ->orWhere('whatsapp', $login)
            ->orWhere('email', $login)
            ->orWhere('cnic', $login)
            ->orWhere('passport_no', $login)
            ->first();

        if (
            ! $customer
            || (
                $customer->customer_type
                ?? 'Customer'
            )
            !== 'Agent'
            || blank(
                $customer->portal_password,
            )
            || ! Hash::check(
                $data['password'],
                (string) $customer
                    ->portal_password,
            )
            || (
                $customer->status
                ?? 'Active'
            )
            !== 'Active'
        ) {
            return back()
                ->withInput(
                    $request->except(
                        'password',
                    ),
                )
                ->with(
                    'error',
                    'Login details ghalat hain ya portal access available nahi hai.',
                );
        }

        $request->session()
            ->regenerate();

        $customer->update([
            'last_portal_login_at' => now(),
        ]);

        session([
            'customer_portal_id' => $customer->id,
            'customer_portal_name' => $customer->name,
            'customer_portal_last_activity' => time(),
            'customer_portal_password_fingerprint' =>
                $this->portalPasswordFingerprint(
                    $customer,
                ),
        ]);

        return redirect()->route('customer.portal.dashboard');
    }

    public function register(): RedirectResponse
    {
        return redirect()
            ->route('customer.portal.login')
            ->with('error', 'Agent Portal account sirf office/admin se create hota hai. Login details ke liye Karwan E Al Saeed se contact karein.');
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        return redirect()
            ->route('customer.portal.login')
            ->with('error', 'Public registration disabled hai. Agent account sirf admin panel se create hota hai.');
    }

    public function dashboard(): View
    {
        $customer = $this->portalCustomer();

        return view('customer-portal.dashboard', [
            'customer' => $customer,
            'ledgerBalance' => ($this->customerStatement((int) $customer->id)['stats']['closing_balance'] ?? 0),
            'notificationsCount' => $this->unreadNotificationsCount($customer),
            'supportTicketsCount' => SupportTicket::query()->where('customer_id', $customer->id)->count(),
            'paymentUploadsCount' => CustomerPaymentUpload::query()->where('customer_id', $customer->id)->count(),
        ]);
    }

    public function profile(): View
    {
        return view(
            'customer-portal.profile',
            [
                'customer' =>
                    $this->portalCustomer(),
            ],
        );
    }

    public function updateProfile(
        Request $request,
    ): RedirectResponse {
        $customer =
            $this->portalCustomer();

        $data =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'father_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'mobile' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'whatsapp' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'date_of_birth' => [
                    'nullable',
                    'date',
                    'before:today',
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ]);

        foreach (
            [
                'name',
                'father_name',
                'mobile',
                'whatsapp',
                'address',
                'city',
                'country',
            ]
            as $field
        ) {
            $value =
                trim(
                    (string) (
                        $data[$field]
                        ?? ''
                    ),
                );

            $data[$field] =
                $value === ''
                    ? null
                    : $value;
        }

        $data['name'] =
            (string) $data['name'];

        $data['mobile'] =
            (string) $data['mobile'];

        $email =
            Str::lower(
                trim(
                    (string) (
                        $data['email']
                        ?? ''
                    ),
                ),
            );

        $data['email'] =
            $email === ''
                ? null
                : $email;

        $this->ensureUniquePortalIdentifiers(
            $customer,
            $data,
        );

        $customer->update(
            $data,
        );

        $request->session()
            ->put(
                'customer_portal_name',
                $customer->name,
            );

        return redirect()
            ->route(
                'customer.portal.profile',
            )
            ->with(
                'success',
                'Profile successfully update ho gaya hai.',
            );
    }

    public function updatePassword(
        Request $request,
    ): RedirectResponse {
        $customer =
            $this->portalCustomer();

        $data =
            $request->validate([
                'current_password' => [
                    'required',
                    'string',
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->numbers(),
                ],
            ]);

        if (
            ! Hash::check(
                $data['current_password'],
                (string) $customer
                    ->portal_password,
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' =>
                    'Current portal password ghalat hai.',
            ]);
        }

        $customer->forceFill([
            'portal_password' =>
                Hash::make(
                    $data['password'],
                ),
        ])->save();

        $request->session()
            ->regenerate();

        $request->session()
            ->put([
                'customer_portal_id' =>
                    $customer->id,

                'customer_portal_name' =>
                    $customer->name,

                'customer_portal_last_activity' =>
                    time(),

                'customer_portal_password_fingerprint' =>
                    $this->portalPasswordFingerprint(
                        $customer,
                    ),
            ]);

        return redirect()
            ->route(
                'customer.portal.profile',
            )
            ->with(
                'success',
                'Portal password successfully change ho gaya hai.',
            );
    }

    public function visaApplications(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.list',
            [
                'customer' =>
                    $customer,

                'heading' =>
                    'Visa Applications',

                'columns' => [
                    [
                        'key' =>
                            'application_no',

                        'label' =>
                            'Application No',
                    ],

                    [
                        'key' =>
                            'passport_no',

                        'label' =>
                            'Passport No',
                    ],

                    [
                        'key' =>
                            'travel_date',

                        'label' =>
                            'Travel Date',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'return_date',

                        'label' =>
                            'Return Date',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'status',

                        'label' =>
                            'Status',

                        'type' =>
                            'status',
                    ],

                    [
                        'key' =>
                            'total_amount',

                        'label' =>
                            'Total',

                        'type' =>
                            'money',
                    ],

                    [
                        'key' =>
                            'balance_amount',

                        'label' =>
                            'Balance',

                        'type' =>
                            'money',
                    ],
                ],

                'records' =>
                    $this->rowsFromTable(
                        'visa_applications',
                        (int) $customer->id,
                    ),
            ],
        );
    }

    public function flightBookings(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.list',
            [
                'customer' =>
                    $customer,

                'heading' =>
                    'Flight Bookings',

                'columns' => [
                    [
                        'key' =>
                            'booking_no',

                        'label' =>
                            'Booking No',
                    ],

                    [
                        'key' =>
                            'pnr',

                        'label' =>
                            'PNR',
                    ],

                    [
                        'key' =>
                            'ticket_no',

                        'label' =>
                            'Ticket No',
                    ],

                    [
                        'key' =>
                            'departure_date',

                        'label' =>
                            'Departure',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'return_date',

                        'label' =>
                            'Return',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'status',

                        'label' =>
                            'Status',

                        'type' =>
                            'status',
                    ],

                    [
                        'key' =>
                            'total_amount',

                        'label' =>
                            'Total',

                        'type' =>
                            'money',
                    ],

                    [
                        'key' =>
                            'balance_amount',

                        'label' =>
                            'Balance',

                        'type' =>
                            'money',
                    ],
                ],

                'records' =>
                    $this->rowsFromTable(
                        'flight_bookings',
                        (int) $customer->id,
                    ),
            ],
        );
    }

    public function umrahBookings(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.list',
            [
                'customer' =>
                    $customer,

                'heading' =>
                    'Umrah Bookings',

                'columns' => [
                    [
                        'key' =>
                            'booking_no',

                        'label' =>
                            'Booking No',
                    ],

                    [
                        'key' =>
                            'package_name',

                        'label' =>
                            'Package',
                    ],

                    [
                        'key' =>
                            'travel_date',

                        'label' =>
                            'Travel Date',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'return_date',

                        'label' =>
                            'Return Date',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'no_of_persons',

                        'label' =>
                            'Persons',
                    ],

                    [
                        'key' =>
                            'status',

                        'label' =>
                            'Status',

                        'type' =>
                            'status',
                    ],

                    [
                        'key' =>
                            'package_amount',

                        'label' =>
                            'Package Amount',

                        'type' =>
                            'money',
                    ],

                    [
                        'key' =>
                            'balance_amount',

                        'label' =>
                            'Balance',

                        'type' =>
                            'money',
                    ],
                ],

                'records' =>
                    $this->rowsFromTable(
                        'umrah_bookings',
                        (int) $customer->id,
                    ),
            ],
        );
    }

    public function hotelBookings(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.list',
            [
                'customer' =>
                    $customer,

                'heading' =>
                    'Hotel Bookings',

                'columns' => [
                    [
                        'key' =>
                            'booking_no',

                        'label' =>
                            'Booking No',
                    ],

                    [
                        'key' =>
                            'hotel_name',

                        'label' =>
                            'Hotel',
                    ],

                    [
                        'key' =>
                            'city',

                        'label' =>
                            'City',
                    ],

                    [
                        'key' =>
                            'check_in_date',

                        'label' =>
                            'Check In',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'check_out_date',

                        'label' =>
                            'Check Out',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'status',

                        'label' =>
                            'Status',

                        'type' =>
                            'status',
                    ],

                    [
                        'key' =>
                            'total_amount',

                        'label' =>
                            'Total',

                        'type' =>
                            'money',
                    ],

                    [
                        'key' =>
                            'balance_amount',

                        'label' =>
                            'Balance',

                        'type' =>
                            'money',
                    ],
                ],

                'records' =>
                    $this->rowsFromTable(
                        'hotel_bookings',
                        (int) $customer->id,
                    ),
            ],
        );
    }

    public function invoices(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.list',
            [
                'customer' =>
                    $customer,

                'heading' =>
                    'Invoices',

                'columns' => [
                    [
                        'key' =>
                            'invoice_no',

                        'label' =>
                            'Invoice No',
                    ],

                    [
                        'key' =>
                            'invoice_date',

                        'label' =>
                            'Invoice Date',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'due_date',

                        'label' =>
                            'Due Date',

                        'type' =>
                            'date',
                    ],

                    [
                        'key' =>
                            'service_type',

                        'label' =>
                            'Service',
                    ],

                    [
                        'key' =>
                            'status',

                        'label' =>
                            'Status',

                        'type' =>
                            'status',
                    ],

                    [
                        'key' =>
                            'total_amount',

                        'label' =>
                            'Total',

                        'type' =>
                            'money',
                    ],

                    [
                        'key' =>
                            'paid_amount',

                        'label' =>
                            'Paid',

                        'type' =>
                            'money',
                    ],

                    [
                        'key' =>
                            'balance_amount',

                        'label' =>
                            'Balance',

                        'type' =>
                            'money',
                    ],
                ],

                'records' =>
                    $this->rowsFromTable(
                        'invoices',
                        (int) $customer->id,
                    ),
            ],
        );
    }

    public function documents(): View
    {
        $customer =
            $this->portalCustomer();

        $documents =
            Schema::hasTable(
                'customer_documents',
            )
                ? CustomerDocument::query()
                    ->where(
                        'customer_id',
                        $customer->id,
                    )
                    ->orderByDesc(
                        'id',
                    )
                    ->paginate(
                        15,
                        [
                            '*',
                        ],
                        'page',
                    )
                    ->withQueryString()
                : $this->emptyPortalPaginator(
                    15,
                    'page',
                );

        return view(
            'customer-portal.documents',
            [
                'customer' =>
                    $customer,

                'documents' =>
                    $documents,
            ],
        );
    }

    public function vouchers(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.list',
            [
                'customer' =>
                    $customer,

                'heading' =>
                    'Vouchers',

                'columns' => [
                    [
                        'key' =>
                            'voucher_no',

                        'label' =>
                            'Voucher No',
                    ],

                    [
                        'key' =>
                            'booking_reference',

                        'label' =>
                            'Booking Reference',
                    ],

                    [
                        'key' =>
                            'lead_guest',

                        'label' =>
                            'Lead Guest',
                    ],

                    [
                        'key' =>
                            'adults',

                        'label' =>
                            'Adults',
                    ],

                    [
                        'key' =>
                            'children',

                        'label' =>
                            'Children',
                    ],

                    [
                        'key' =>
                            'status',

                        'label' =>
                            'Status',

                        'type' =>
                            'status',
                    ],

                    [
                        'key' =>
                            'created_at',

                        'label' =>
                            'Created',

                        'type' =>
                            'date',
                    ],
                ],

                'records' =>
                    $this->rowsFromTable(
                        'umrah_vouchers',
                        (int) $customer->id,
                    ),
            ],
        );
    }

    public function ledger(): View
    {
        $customer =
            $this->portalCustomer();

        $statement =
            $this->customerStatement(
                (int) $customer->id,
            );

        return view(
            'customer-portal.ledger',
            [
                'customer' =>
                    $customer,

                'statement' =>
                    $statement,

                'rows' =>
                    collect(
                        $statement['rows']
                        ?? [],
                    ),

                'closingBalance' =>
                    (float) (
                        $statement['stats']['closing_balance']
                        ?? 0
                    ),
            ],
        );
    }

    public function paymentUploads(): View
    {
        $customer =
            $this->portalCustomer();

        $uploads =
            CustomerPaymentUpload::query()
                ->select([
                    'id',
                    'customer_id',
                    'upload_no',
                    'service_type',
                    'reference_no',
                    'amount',
                    'payment_method',
                    'payment_date',
                    'transaction_id',
                    'receipt_file',
                    'status',
                    'customer_notes',
                    'rejection_reason',
                    'created_at',
                ])
                ->where(
                    'customer_id',
                    $customer->id,
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    15,
                )
                ->withQueryString();

        return view(
            'customer-portal.payment-uploads',
            [
                'customer' =>
                    $customer,

                'uploads' =>
                    $uploads,
            ],
        );
    }

    public function storePaymentUpload(
        Request $request,
    ): RedirectResponse {
        $customer =
            $this->portalCustomer();

        $data =
            $request->validate([
                'service_type' => [
                    'required',
                    Rule::in([
                        'Visa',
                        'Flight',
                        'Umrah',
                        'Hotel',
                        'Insurance',
                        'Pakistan Tours',
                        'Other',
                    ]),
                ],

                'reference_no' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:100000000',
                ],

                'payment_method' => [
                    'required',
                    Rule::in([
                        'Bank Transfer',
                        'Cash Deposit',
                        'JazzCash',
                        'EasyPaisa',
                        'Cheque',
                        'Credit Card',
                        'Other',
                    ]),
                ],

                'payment_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],

                'transaction_id' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'receipt_file' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf,webp',
                    'max:5120',
                ],

                'customer_notes' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],
            ]);

        foreach (
            [
                'reference_no',
                'transaction_id',
                'customer_notes',
            ]
            as $field
        ) {
            $value =
                trim(
                    (string) (
                        $data[$field]
                        ?? ''
                    ),
                );

            $data[$field] =
                $value === ''
                    ? null
                    : $value;
        }

        $receiptPath =
            $request->file(
                'receipt_file',
            )->store(
                'customer/payment-receipts',
                'local',
            );

        if (! is_string($receiptPath)) {
            throw new \RuntimeException(
                'Payment receipt private storage mein save nahi hui.',
            );
        }

        try {
            $data['receipt_file'] =
                $receiptPath;

            $data['customer_id'] =
                $customer->id;

            $data['status'] =
                'Pending';

            CustomerPaymentUpload::query()
                ->create(
                    $data,
                );
        } catch (Throwable $exception) {
            Storage::disk(
                'local',
            )->delete(
                $receiptPath,
            );

            throw $exception;
        }

        return redirect()
            ->route(
                'customer.portal.payment_uploads',
            )
            ->with(
                'success',
                'Payment proof securely upload ho gaya hai. Admin approval ke baad ledger update hoga.',
            );
    }

    public function familyMembers(): View
    {
        $customer =
            $this->portalCustomer();

        $members =
            CustomerFamilyMember::query()
                ->where(
                    'customer_id',
                    $customer->id,
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    15,
                )
                ->withQueryString();

        return view(
            'customer-portal.family-members',
            compact(
                'customer',
                'members',
            ),
        );
    }

    public function storeFamilyMember(Request $request): RedirectResponse
    {
        $customer = $this->portalCustomer();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:255'],
            'cnic' => ['nullable', 'string', 'max:255'],
            'passport_no' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['customer_id'] = $customer->id;
        $data['status'] = 'Active';

        CustomerFamilyMember::create($data);

        return back()->with('success', 'Family member add ho gaya.');
    }

    public function deleteFamilyMember(CustomerFamilyMember $familyMember): RedirectResponse
    {
        $customer = $this->portalCustomer();

        abort_unless((int) $familyMember->customer_id === (int) $customer->id, 403);

        $familyMember->delete();

        return back()->with('success', 'Family member delete ho gaya.');
    }

    public function notifications(): View
    {
        $customer =
            $this->portalCustomer();

        $notifications =
            $this->notificationsQuery(
                $customer,
            )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    12,
                )
                ->withQueryString();

        $notificationIds =
            $notifications
                ->getCollection()
                ->pluck(
                    'id',
                )
                ->map(
                    fn ($id): int =>
                        (int) $id,
                )
                ->values()
                ->all();

        $readNotificationIds = [];

        if ($notificationIds !== []) {
            $readNotificationIds =
                CustomerNotificationRead::query()
                    ->where(
                        'customer_id',
                        $customer->id,
                    )
                    ->whereNotNull(
                        'read_at',
                    )
                    ->whereIn(
                        'customer_notification_id',
                        $notificationIds,
                    )
                    ->pluck(
                        'customer_notification_id',
                    )
                    ->map(
                        fn ($id): int =>
                            (int) $id,
                    )
                    ->values()
                    ->all();
        }

        return view(
            'customer-portal.notifications',
            [
                'customer' =>
                    $customer,

                'notifications' =>
                    $notifications,

                'readNotificationIds' =>
                    $readNotificationIds,

                'unreadCount' =>
                    $this->unreadNotificationsCount(
                        $customer,
                    ),
            ],
        );
    }

    public function markNotificationRead(CustomerNotification $customerNotification): RedirectResponse
    {
        $customer = $this->portalCustomer();

        abort_unless($this->notificationIsVisibleToCustomer($customerNotification, $customer), 403);

        $this->markNotificationAsRead($customerNotification, $customer);

        return back()->with('success', 'Notification read mark ho gayi.');
    }

    public function markAllNotificationsRead(): RedirectResponse
    {
        $customer =
            $this->portalCustomer();

        $this->notificationsQuery(
            $customer,
        )
            ->lazyById(
                200,
            )
            ->each(function (
                CustomerNotification $notification,
            ) use (
                $customer,
            ): void {
                $this->markNotificationAsRead(
                    $notification,
                    $customer,
                );
            });

        return back()
            ->with(
                'success',
                'All notifications read mark ho gayi hain.',
            );
    }

    public function supportTickets(): View
    {
        $customer =
            $this->portalCustomer();

        $tickets =
            SupportTicket::query()
                ->where(
                    'customer_id',
                    $customer->id,
                )
                ->orderByDesc(
                    'id',
                )
                ->paginate(
                    15,
                )
                ->withQueryString();

        return view(
            'customer-portal.support-tickets',
            compact(
                'customer',
                'tickets',
            ),
        );
    }

    public function storeSupportTicket(Request $request): RedirectResponse
    {
        $customer = $this->portalCustomer();

        $data = $request->validate([
            'service_type' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', 'string', 'max:255'],
        ]);

        $ticket = SupportTicket::create([
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'service_type' => $data['service_type'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'priority' => $data['priority'] ?? 'Normal',
            'status' => 'Open',
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'customer_id' => $customer->id,
            'message' => $data['message'],
            'is_admin' => false,
        ]);

        return redirect()
            ->route('customer.portal.support.show', $ticket)
            ->with('success', 'Support ticket create ho gaya. Ticket No: ' . $ticket->ticket_no);
    }

    public function showSupportTicket(SupportTicket $supportTicket): View
    {
        $customer = $this->portalCustomer();

        abort_unless((int) $supportTicket->customer_id === (int) $customer->id, 403);

        $supportTicket->load('replies');

        return view('customer-portal.support-ticket-show', [
            'customer' => $customer,
            'ticket' => $supportTicket,
        ]);
    }

    public function replySupportTicket(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $customer = $this->portalCustomer();

        abort_unless((int) $supportTicket->customer_id === (int) $customer->id, 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $supportTicket->id,
            'customer_id' => $customer->id,
            'message' => $data['message'],
            'is_admin' => false,
        ]);

        if ($supportTicket->status === 'Closed') {
            $supportTicket->update([
                'status' => 'Open',
                'closed_at' => null,
            ]);
        }

        return back()->with('success', 'Reply send ho gaya.');
    }

    public function bookingTracking(): View
    {
        $customer =
            $this->portalCustomer();

        return view(
            'customer-portal.booking-tracking',
            [
                'customer' =>
                    $customer,

                'visaApplications' =>
                    $this->rowsFromTable(
                        'visa_applications',
                        (int) $customer->id,
                        'visa_page',
                        5,
                    ),

                'flightBookings' =>
                    $this->rowsFromTable(
                        'flight_bookings',
                        (int) $customer->id,
                        'flight_page',
                        5,
                    ),

                'umrahBookings' =>
                    $this->rowsFromTable(
                        'umrah_bookings',
                        (int) $customer->id,
                        'umrah_page',
                        5,
                    ),

                'hotelBookings' =>
                    $this->rowsFromTable(
                        'hotel_bookings',
                        (int) $customer->id,
                        'hotel_page',
                        5,
                    ),
            ],
        );
    }

    public function logout(
        Request $request,
    ): RedirectResponse {
        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        return redirect()
            ->route(
                'customer.portal.login',
            )
            ->with(
                'success',
                'You have been logged out successfully.',
            );
    }

    private function ensureUniquePortalIdentifiers(
        Customer $customer,
        array $data,
    ): void {
        $errors = [];

        foreach (
            [
                'mobile',
                'whatsapp',
                'email',
            ]
            as $field
        ) {
            $value =
                trim(
                    (string) (
                        $data[$field]
                        ?? ''
                    ),
                );

            if ($value === '') {
                continue;
            }

            $exists =
                Customer::query()
                    ->whereKeyNot(
                        $customer->getKey(),
                    )
                    ->where(function (
                        Builder $query,
                    ) use (
                        $value,
                    ): void {
                        $query
                            ->where(
                                'mobile',
                                $value,
                            )
                            ->orWhere(
                                'whatsapp',
                                $value,
                            )
                            ->orWhere(
                                'email',
                                $value,
                            )
                            ->orWhere(
                                'cnic',
                                $value,
                            )
                            ->orWhere(
                                'passport_no',
                                $value,
                            );
                    })
                    ->exists();

            if ($exists) {
                $errors[$field] =
                    'Yeh login/contact detail kisi doosre portal account ke saath registered hai.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors,
            );
        }
    }

    private function portalPasswordFingerprint(
        Customer $customer,
    ): string {
        return hash(
            'sha256',
            (string) $customer
                ->portal_password,
        );
    }
    private function portalCustomer(): Customer
    {
        $customer =
            Customer::query()
                ->find(
                    (int) session(
                        'customer_portal_id',
                    ),
                );

        abort_unless(
            $customer
            && (
                $customer->customer_type
                ?? 'Customer'
            )
            === 'Agent'
            && (
                $customer->status
                ?? 'Active'
            )
            === 'Active'
            && filled(
                $customer->portal_password,
            ),
            403,
            'Portal customer session valid nahi hai.',
        );

        return $customer;
    }

    private function rowsFromTable(
        string $table,
        int $customerId,
        string $pageName = 'page',
        int $perPage = 15,
    ): LengthAwarePaginator {
        if (
            ! Schema::hasTable(
                $table,
            )
            || ! Schema::hasColumn(
                $table,
                'customer_id',
            )
        ) {
            return $this->emptyPortalPaginator(
                $perPage,
                $pageName,
            );
        }

        return DB::table(
            $table,
        )
            ->where(
                'customer_id',
                $customerId,
            )
            ->orderByDesc(
                'id',
            )
            ->paginate(
                $perPage,
                [
                    '*',
                ],
                $pageName,
            )
            ->withQueryString();
    }

    private function emptyPortalPaginator(
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator {
        return new LengthAwarePaginator(
            [],
            0,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(
                $pageName,
            ),
            [
                'path' =>
                    request()->url(),

                'query' =>
                    request()->query(),

                'pageName' =>
                    $pageName,
            ],
        );
    }

    private function notificationsQuery(Customer $customer)
    {
        return CustomerNotification::query()
            ->where('status', 'Active')
            ->where(function ($query) use ($customer): void {
                $query->whereNull('customer_id')
                    ->orWhere('customer_id', $customer->id);
            })
            ->where(function ($query): void {
                $query->whereNull('visible_from')
                    ->orWhereDate('visible_from', '<=', now()->toDateString());
            })
            ->where(function ($query): void {
                $query->whereNull('visible_until')
                    ->orWhereDate('visible_until', '>=', now()->toDateString());
            });
    }

    private function unreadNotificationsCount(Customer $customer): int
    {
        $notificationIds = $this->notificationsQuery($customer)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->toArray();

        if (count($notificationIds) === 0) {
            return 0;
        }

        $readIds = CustomerNotificationRead::query()
            ->where('customer_id', $customer->id)
            ->whereNotNull('read_at')
            ->whereIn('customer_notification_id', $notificationIds)
            ->pluck('customer_notification_id')
            ->map(fn ($id): int => (int) $id)
            ->toArray();

        return count(array_diff($notificationIds, $readIds));
    }

    private function notificationIsVisibleToCustomer(CustomerNotification $notification, Customer $customer): bool
    {
        if ($notification->status !== 'Active') {
            return false;
        }

        if ($notification->customer_id && (int) $notification->customer_id !== (int) $customer->id) {
            return false;
        }

        if ($notification->visible_from && $notification->visible_from->toDateString() > now()->toDateString()) {
            return false;
        }

        if ($notification->visible_until && $notification->visible_until->toDateString() < now()->toDateString()) {
            return false;
        }

        return true;
    }

    private function markNotificationAsRead(CustomerNotification $notification, Customer $customer): void
    {
        CustomerNotificationRead::query()->updateOrCreate(
            [
                'customer_id' => $customer->id,
                'customer_notification_id' => $notification->id,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    private function customerStatement(
        int $customerId,
    ): array {
        return app(
            UnifiedCustomerStatementService::class,
        )->generate(
            customerId:
                $customerId,

            dateFrom:
                '1900-01-01',

            dateTo:
                '2999-12-31',

            serviceType:
                'all',
        );
    }
}