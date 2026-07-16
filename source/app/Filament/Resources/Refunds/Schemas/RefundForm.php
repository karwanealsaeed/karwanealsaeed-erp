<?php

namespace App\Filament\Resources\Refunds\Schemas;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\Refund;
use App\Models\UmrahBooking;
use App\Models\VisaApplication;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('refund_no')
                    ->label('Refund No')
                    ->default(fn (): string => self::getNextRefundNo())
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255),

                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('service_reference', null);
                    })
                    ->required(),

                DatePicker::make('refund_date')
                    ->label('Refund Date')
                    ->default(now())
                    ->required(),

                Select::make('service_type')
                    ->label('Service Type')
                    ->options([
                        'Visa' => 'Visa',
                        'Flight' => 'Flight',
                        'Umrah' => 'Umrah',
                        'Hotel' => 'Hotel',
                        'Other' => 'Other',
                    ])
                    ->default('Other')
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('service_reference', null);
                    })
                    ->required(),

                Select::make('service_reference')
                    ->label('Service Reference')
                    ->placeholder('Pehle Customer aur Service Type select karein')
                    ->options(fn (Get $get): array => self::getServiceReferenceOptions($get))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('customer_id')) || blank($get('service_type')) || $get('service_type') === 'Other')
                    ->helperText('Customer aur Service Type ke hisaab se booking/reference select hogi.'),

                TextInput::make('refund_amount')
                    ->label('Refund Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required(),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank' => 'Bank',
                        'JazzCash' => 'JazzCash',
                        'EasyPaisa' => 'EasyPaisa',
                        'Credit Card' => 'Credit Card',
                        'Cheque' => 'Cheque',
                    ])
                    ->default('Cash')
                    ->required(),

                TextInput::make('reference_no')
                    ->label('Payment Reference No')
                    ->maxLength(255),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Paid' => 'Paid',
                        'Rejected' => 'Rejected',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->default('Pending')
                    ->required(),

                Textarea::make('reason')
                    ->label('Refund Reason')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function getNextRefundNo(): string
    {
        $lastRefund = Refund::latest('id')->first();

        $nextNumber = $lastRefund ? $lastRefund->id + 1 : 1;

        return 'RF-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private static function getServiceReferenceOptions(Get $get): array
    {
        $customerId = $get('customer_id');
        $serviceType = $get('service_type');

        if (! $customerId || ! $serviceType) {
            return [];
        }

        if ($serviceType === 'Visa') {
            return VisaApplication::query()
                ->where('customer_id', $customerId)
                ->latest('id')
                ->get()
                ->mapWithKeys(function (VisaApplication $visaApplication): array {
                    $label = $visaApplication->application_no;

                    if ($visaApplication->country?->name) {
                        $label .= ' - ' . $visaApplication->country->name;
                    }

                    if ($visaApplication->status) {
                        $label .= ' - ' . $visaApplication->status;
                    }

                    return [
                        $visaApplication->application_no => $label,
                    ];
                })
                ->toArray();
        }

        if ($serviceType === 'Flight') {
            return FlightBooking::query()
                ->where('customer_id', $customerId)
                ->latest('id')
                ->get()
                ->mapWithKeys(function (FlightBooking $flightBooking): array {
                    $label = $flightBooking->booking_no;

                    if ($flightBooking->pnr) {
                        $label .= ' - PNR: ' . $flightBooking->pnr;
                    }

                    if ($flightBooking->status) {
                        $label .= ' - ' . $flightBooking->status;
                    }

                    return [
                        $flightBooking->booking_no => $label,
                    ];
                })
                ->toArray();
        }

        if ($serviceType === 'Umrah') {
            return UmrahBooking::query()
                ->where('customer_id', $customerId)
                ->latest('id')
                ->get()
                ->mapWithKeys(function (UmrahBooking $umrahBooking): array {
                    $label = $umrahBooking->booking_no;

                    if ($umrahBooking->package_name) {
                        $label .= ' - ' . $umrahBooking->package_name;
                    }

                    if ($umrahBooking->status) {
                        $label .= ' - ' . $umrahBooking->status;
                    }

                    return [
                        $umrahBooking->booking_no => $label,
                    ];
                })
                ->toArray();
        }

        if ($serviceType === 'Hotel') {
            return HotelBooking::query()
                ->where('customer_id', $customerId)
                ->latest('id')
                ->get()
                ->mapWithKeys(function (HotelBooking $hotelBooking): array {
                    $label = $hotelBooking->booking_no;

                    if ($hotelBooking->hotel_name) {
                        $label .= ' - ' . $hotelBooking->hotel_name;
                    }

                    if ($hotelBooking->city) {
                        $label .= ' - ' . $hotelBooking->city;
                    }

                    if ($hotelBooking->status) {
                        $label .= ' - ' . $hotelBooking->status;
                    }

                    return [
                        $hotelBooking->booking_no => $label,
                    ];
                })
                ->toArray();
        }

        return [];
    }
}
