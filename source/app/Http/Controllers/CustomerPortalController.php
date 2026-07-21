<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerFamilyMember;
use App\Models\CustomerNotification;
use App\Models\CustomerNotificationRead;
use App\Models\CustomerPaymentUpload;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

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
            'ledgerBalance' => $this->customerLedgerRows($customer->id)->last()['balance'] ?? 0,
            'notificationsCount' => $this->unreadNotificationsCount($customer),
            'supportTicketsCount' => SupportTicket::query()->where('customer_id', $customer->id)->count(),
            'paymentUploadsCount' => CustomerPaymentUpload::query()->where('customer_id', $customer->id)->count(),
        ]);
    }

    public function profile(): View
    {
        return view('customer-portal.profile', [
            'customer' => $this->portalCustomer(),
        ]);
    }

    public function visaApplications(): View
    {
        $customer = $this->portalCustomer();

        return view('customer-portal.list', [
            'customer' => $customer,
            'title' => 'Visa Applications',
            'records' => $this->rowsFromTable('visa_applications', $customer->id),
        ]);
    }

    public function flightBookings(): View
    {
        $customer = $this->portalCustomer();

        return view('customer-portal.list', [
            'customer' => $customer,
            'title' => 'Flight Bookings',
            'records' => $this->rowsFromTable('flight_bookings', $customer->id),
        ]);
    }

    public function umrahBookings(): View
    {
        $customer = $this->portalCustomer();

        return view('customer-portal.list', [
            'customer' => $customer,
            'title' => 'Umrah Bookings',
            'records' => $this->rowsFromTable('umrah_bookings', $customer->id),
        ]);
    }

    public function hotelBookings(): View
    {
        $customer = $this->portalCustomer();

        return view('customer-portal.list', [
            'customer' => $customer,
            'title' => 'Hotel Bookings',
            'records' => $this->rowsFromTable('hotel_bookings', $customer->id),
        ]);
    }

    public function invoices(): View
    {
        $customer = $this->portalCustomer();

        return view('customer-portal.list', [
            'customer' => $customer,
            'title' => 'Invoices',
            'records' => $this->rowsFromTable('invoices', $customer->id),
        ]);
    }

    public function documents(): View
    {
        $customer = $this->portalCustomer();

        $documents = collect();

        if (Schema::hasTable('customer_documents')) {
            $documents = DB::table('customer_documents')
                ->where('customer_id', $customer->id)
                ->latest('id')
                ->get();
        }

        return view('customer-portal.documents', [
            'customer' => $customer,
            'documents' => $documents,
        ]);
    }

    public function vouchers(): View
    {
        $customer = $this->portalCustomer();

        $records = collect();

        if (Schema::hasTable('umrah_vouchers')) {
            $records = DB::table('umrah_vouchers')
                ->where('customer_id', $customer->id)
                ->latest('id')
                ->get();
        }

        return view('customer-portal.list', [
            'customer' => $customer,
            'title' => 'Vouchers',
            'records' => $records,
        ]);
    }

    public function ledger(): View
    {
        $customer = $this->portalCustomer();
        $rows = $this->customerLedgerRows($customer->id);

        return view('customer-portal.ledger', [
            'customer' => $customer,
            'rows' => $rows,
            'closingBalance' => $rows->last()['balance'] ?? 0,
        ]);
    }

    public function paymentUploads(): View
    {
        $customer = $this->portalCustomer();

        $uploads = CustomerPaymentUpload::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return view('customer-portal.payment-uploads', compact('customer', 'uploads'));
    }

    public function storePaymentUpload(Request $request): RedirectResponse
    {
        $customer = $this->portalCustomer();

        $data = $request->validate([
            'service_type' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'max:255'],
            'payment_date' => ['required', 'date'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'receipt_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
            'customer_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($request->hasFile('receipt_file')) {
            $data['receipt_file'] = $request->file('receipt_file')->store('customer-payment-uploads', 'public');
        }

        $data['customer_id'] = $customer->id;
        $data['status'] = 'Pending';

        CustomerPaymentUpload::create($data);

        return back()->with('success', 'Payment proof upload ho gaya hai. Admin approval ke baad ledger update ho jayega.');
    }

    public function familyMembers(): View
    {
        $customer = $this->portalCustomer();

        $members = CustomerFamilyMember::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return view('customer-portal.family-members', compact('customer', 'members'));
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
        $customer = $this->portalCustomer();

        $notifications = $this->notificationsQuery($customer)
            ->latest()
            ->get();

        $readNotificationIds = CustomerNotificationRead::query()
            ->where('customer_id', $customer->id)
            ->whereNotNull('read_at')
            ->pluck('customer_notification_id')
            ->map(fn ($id): int => (int) $id)
            ->toArray();

        return view('customer-portal.notifications', [
            'customer' => $customer,
            'notifications' => $notifications,
            'readNotificationIds' => $readNotificationIds,
            'unreadCount' => $this->unreadNotificationsCount($customer),
        ]);
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
        $customer = $this->portalCustomer();

        $this->notificationsQuery($customer)
            ->get()
            ->each(function (CustomerNotification $notification) use ($customer): void {
                $this->markNotificationAsRead($notification, $customer);
            });

        return back()->with('success', 'All notifications read mark ho gayi hain.');
    }

    public function supportTickets(): View
    {
        $customer = $this->portalCustomer();

        $tickets = SupportTicket::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return view('customer-portal.support-tickets', compact('customer', 'tickets'));
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
        $customer = $this->portalCustomer();

        return view('customer-portal.booking-tracking', [
            'customer' => $customer,
            'visaApplications' => $this->rowsFromTable('visa_applications', $customer->id),
            'flightBookings' => $this->rowsFromTable('flight_bookings', $customer->id),
            'umrahBookings' => $this->rowsFromTable('umrah_bookings', $customer->id),
            'hotelBookings' => $this->rowsFromTable('hotel_bookings', $customer->id),
        ]);
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

    private function rowsFromTable(string $table, int $customerId): Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'customer_id')) {
            return collect();
        }

        return DB::table($table)
            ->where('customer_id', $customerId)
            ->latest('id')
            ->get();
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

    private function customerLedgerRows(int $customerId): Collection
    {
        $rows = collect();

        foreach ([
            'invoices' => ['Invoice', ['invoice_no', 'reference_no'], ['total_amount', 'grand_total', 'net_amount', 'amount'], ['invoice_date', 'date', 'created_at']],
            'visa_applications' => ['Visa Application', ['application_no', 'reference_no'], ['total_fee', 'fee', 'amount'], ['application_date', 'date', 'created_at']],
            'flight_bookings' => ['Flight Booking', ['booking_no', 'reference_no'], ['total_amount', 'amount'], ['booking_date', 'date', 'created_at']],
            'umrah_bookings' => ['Umrah Booking', ['booking_no', 'reference_no'], ['package_amount', 'total_amount', 'amount'], ['booking_date', 'date', 'created_at']],
            'hotel_bookings' => ['Hotel Booking', ['booking_no', 'reference_no'], ['total_amount', 'amount'], ['booking_date', 'date', 'created_at']],
        ] as $table => [$label, $refColumns, $amountColumns, $dateColumns]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'customer_id')) {
                continue;
            }

            foreach (DB::table($table)->where('customer_id', $customerId)->get() as $record) {
                $amount = $this->firstRecordAmount($record, $amountColumns);

                if ($amount <= 0) {
                    continue;
                }

                $rows->push([
                    'date' => $this->firstRecordDate($record, $dateColumns),
                    'type' => $label,
                    'reference' => $this->firstRecordText($record, $refColumns, strtoupper(substr($table, 0, 2)) . '-' . $record->id),
                    'description' => $label . ' posted',
                    'debit' => $amount,
                    'credit' => 0,
                ]);
            }
        }

        foreach ([
            'payments' => 'Visa Payment',
            'flight_payments' => 'Flight Payment',
            'umrah_payments' => 'Umrah Payment',
            'hotel_payments' => 'Hotel Payment',
        ] as $table => $label) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'customer_id')) {
                continue;
            }

            foreach (DB::table($table)->where('customer_id', $customerId)->get() as $record) {
                $amount = $this->firstRecordAmount($record, ['amount', 'paid_amount', 'payment_amount', 'received_amount']);

                if ($amount <= 0) {
                    continue;
                }

                $rows->push([
                    'date' => $this->firstRecordDate($record, ['payment_date', 'paid_at', 'date', 'created_at']),
                    'type' => $label,
                    'reference' => $this->firstRecordText($record, ['receipt_no', 'payment_no', 'reference_no'], strtoupper(substr($table, 0, 2)) . '-' . $record->id),
                    'description' => 'Payment received',
                    'debit' => 0,
                    'credit' => $amount,
                ]);
            }
        }

        if (Schema::hasTable('customer_payment_uploads')) {
            foreach (
                DB::table('customer_payment_uploads')
                    ->where('customer_id', $customerId)
                    ->where('status', 'Approved')
                    ->get() as $record
            ) {
                $amount = $this->firstRecordAmount($record, ['amount']);

                if ($amount <= 0) {
                    continue;
                }

                $rows->push([
                    'date' => $this->firstRecordDate($record, ['payment_date', 'approved_at', 'created_at']),
                    'type' => 'Approved Payment Upload',
                    'reference' => $this->firstRecordText($record, ['upload_no', 'reference_no'], 'PU-' . $record->id),
                    'description' => 'Payment approved by admin',
                    'debit' => 0,
                    'credit' => $amount,
                ]);
            }
        }

        $balance = 0;

        return $rows
            ->sortBy(fn (array $row): string => $row['date'] . $row['reference'])
            ->values()
            ->map(function (array $row) use (&$balance): array {
                $balance += (float) $row['debit'] - (float) $row['credit'];
                $row['balance'] = $balance;

                return $row;
            });
    }

    private function firstRecordAmount(object $record, array $columns): float
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && is_numeric($record->{$column})) {
                return (float) $record->{$column};
            }
        }

        return 0;
    }

    private function firstRecordText(object $record, array $columns, string $fallback = '-'): string
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return (string) $record->{$column};
            }
        }

        return $fallback;
    }

    private function firstRecordDate(object $record, array $columns): string
    {
        foreach ($columns as $column) {
            if (property_exists($record, $column) && filled($record->{$column})) {
                return Carbon::parse($record->{$column})->format('Y-m-d');
            }
        }

        return now()->format('Y-m-d');
    }
}