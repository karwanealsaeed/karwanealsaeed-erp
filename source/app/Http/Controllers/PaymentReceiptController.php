<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\FlightPayment;
use App\Models\HotelPayment;
use App\Models\Payment;
use App\Models\UmrahPayment;

class PaymentReceiptController extends Controller
{
    public function __invoke(string $type, int $payment)
    {   $permission = match ($type) {
    'visa' => 'visa_payments.print',
    'flight' => 'flight_payments.print',
    'umrah' => 'umrah_payments.print',
    'hotel' => 'hotel_payments.print',
    default => null,
};

abort_unless($permission && (auth()->user()?->hasPermission($permission) ?? false), 403);
        $company = CompanySetting::current();

        $logoUrl = null;

        if ($company->logo) {
            $logoPath = public_path('storage/' . $company->logo);

            if (file_exists($logoPath)) {
                $logoUrl = asset('storage/' . $company->logo);
            }
        }

        $receipt = match ($type) {
            'visa' => $this->visaReceipt($payment),
            'flight' => $this->flightReceipt($payment),
            'umrah' => $this->umrahReceipt($payment),
            'hotel' => $this->hotelReceipt($payment),
            default => abort(404),
        };

        return view('payment-receipts.print', [
            'company' => $company,
            'logoUrl' => $logoUrl,
            'receipt' => $receipt,
        ]);
    }

    private function visaReceipt(int $paymentId): array
    {
        $payment = Payment::with('visaApplication.customer')->findOrFail($paymentId);

        return [
            'receipt_no' => 'VR-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
            'service' => 'Visa Payment',
            'reference_no' => $payment->visaApplication?->application_no ?? $payment->reference_no ?? '-',
            'customer_name' => $payment->visaApplication?->customer?->name ?? '-',
            'customer_mobile' => $payment->visaApplication?->customer?->mobile ?? '-',
            'payment_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'method' => $payment->payment_method ?? '-',
            'external_reference' => $payment->reference_no ?? '-',
            'notes' => $payment->notes ?? '-',
        ];
    }

    private function flightReceipt(int $paymentId): array
    {
        $payment = FlightPayment::with('flightBooking.customer')->findOrFail($paymentId);

        return [
            'receipt_no' => 'FR-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
            'service' => 'Flight Payment',
            'reference_no' => $payment->flightBooking?->booking_no ?? $payment->reference_no ?? '-',
            'customer_name' => $payment->flightBooking?->customer?->name ?? '-',
            'customer_mobile' => $payment->flightBooking?->customer?->mobile ?? '-',
            'payment_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'method' => $payment->payment_method ?? $payment->method ?? '-',
            'external_reference' => $payment->reference_no ?? '-',
            'notes' => $payment->notes ?? '-',
        ];
    }

    private function umrahReceipt(int $paymentId): array
    {
        $payment = UmrahPayment::with('umrahBooking.customer')->findOrFail($paymentId);

        return [
            'receipt_no' => 'UR-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
            'service' => 'Umrah Payment',
            'reference_no' => $payment->umrahBooking?->booking_no ?? $payment->reference_no ?? '-',
            'customer_name' => $payment->umrahBooking?->customer?->name ?? '-',
            'customer_mobile' => $payment->umrahBooking?->customer?->mobile ?? '-',
            'payment_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'method' => $payment->payment_method ?? $payment->method ?? '-',
            'external_reference' => $payment->reference_no ?? '-',
            'notes' => $payment->notes ?? '-',
        ];
    }

    private function hotelReceipt(int $paymentId): array
    {
        $payment = HotelPayment::with('hotelBooking.customer')->findOrFail($paymentId);

        return [
            'receipt_no' => 'HR-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
            'service' => 'Hotel Payment',
            'reference_no' => $payment->hotelBooking?->booking_no ?? $payment->reference_no ?? '-',
            'customer_name' => $payment->hotelBooking?->customer?->name ?? '-',
            'customer_mobile' => $payment->hotelBooking?->customer?->mobile ?? '-',
            'payment_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'method' => $payment->payment_method ?? $payment->method ?? '-',
            'external_reference' => $payment->reference_no ?? '-',
            'notes' => $payment->notes ?? '-',
        ];
    }
}