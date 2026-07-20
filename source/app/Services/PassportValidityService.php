<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;

class PassportValidityService
{
    public const DEFAULT_VALIDITY_MONTHS = 6;

    public const STATUS_VALID =
        'Valid';

    public const STATUS_RENEWAL_REQUIRED =
        'Renewal Required';

    public const STATUS_NOT_VALID_FOR_JOURNEY =
        'Not Valid for Journey';

    public const STATUS_EXPIRED =
        'Expired';

    public const STATUS_MISSING_EXPIRY =
        'Missing Passport Expiry';

    public const STATUS_MISSING_TRAVEL_DATE =
        'Missing Travel Date';

    public function evaluate(
        CarbonInterface|DateTimeInterface|string|null
            $passportExpiry,
        CarbonInterface|DateTimeInterface|string|null
            $travelDate,
        CarbonInterface|DateTimeInterface|string|null
            $returnDate = null,
        int $requiredMonths =
            self::DEFAULT_VALIDITY_MONTHS,
        CarbonInterface|DateTimeInterface|string|null
            $today = null,
    ): array {
        $requiredMonths = max(
            0,
            min(24, $requiredMonths),
        );

        $todayDate = $this->date(
            $today ?? now(),
        );

        $passportExpiryDate =
            $this->nullableDate($passportExpiry);

        $travelDateValue =
            $this->nullableDate($travelDate);

        $returnDateValue =
            $this->nullableDate($returnDate);

        $journeyEnd =
            $returnDateValue
            ?? $travelDateValue;

        $requiredUntil = $journeyEnd
            ? $journeyEnd->addMonthsNoOverflow(
                $requiredMonths,
            )
            : null;

        if (! $passportExpiryDate) {
            return $this->result(
                status:
                    self::STATUS_MISSING_EXPIRY,
                severity: 'danger',
                requiredMonths: $requiredMonths,
                requiredUntil: $requiredUntil,
                passportExpiry: null,
                today: $todayDate,
                validForTravel: false,
                message:
                    'Passport expiry date enter karein.',
            );
        }

        if ($passportExpiryDate->lt($todayDate)) {
            return $this->result(
                status: self::STATUS_EXPIRED,
                severity: 'danger',
                requiredMonths: $requiredMonths,
                requiredUntil: $requiredUntil,
                passportExpiry:
                    $passportExpiryDate,
                today: $todayDate,
                validForTravel: false,
                message:
                    'Passport already expire ho chuka hai.',
            );
        }

        if (! $journeyEnd) {
            return $this->result(
                status:
                    self::STATUS_MISSING_TRAVEL_DATE,
                severity: 'warning',
                requiredMonths: $requiredMonths,
                requiredUntil: null,
                passportExpiry:
                    $passportExpiryDate,
                today: $todayDate,
                validForTravel: false,
                message:
                    'Travel ya return date enter karein.',
            );
        }

        if ($passportExpiryDate->lt($journeyEnd)) {
            return $this->result(
                status:
                    self::STATUS_NOT_VALID_FOR_JOURNEY,
                severity: 'danger',
                requiredMonths: $requiredMonths,
                requiredUntil: $requiredUntil,
                passportExpiry:
                    $passportExpiryDate,
                today: $todayDate,
                validForTravel: false,
                message:
                    'Passport journey complete hone se pehle expire ho raha hai.',
            );
        }

        if (
            $requiredUntil
            && $passportExpiryDate->lt(
                $requiredUntil,
            )
        ) {
            return $this->result(
                status:
                    self::STATUS_RENEWAL_REQUIRED,
                severity: 'warning',
                requiredMonths: $requiredMonths,
                requiredUntil: $requiredUntil,
                passportExpiry:
                    $passportExpiryDate,
                today: $todayDate,
                validForTravel: false,
                message:
                    "Passport journey ke baad {$requiredMonths} months tak valid nahi hai. Renewal required.",
            );
        }

        return $this->result(
            status: self::STATUS_VALID,
            severity: 'success',
            requiredMonths: $requiredMonths,
            requiredUntil: $requiredUntil,
            passportExpiry:
                $passportExpiryDate,
            today: $todayDate,
            validForTravel: true,
            message:
                "Passport {$requiredMonths}-month validity requirement meet karta hai.",
        );
    }

    private function result(
        string $status,
        string $severity,
        int $requiredMonths,
        ?CarbonImmutable $requiredUntil,
        ?CarbonImmutable $passportExpiry,
        CarbonImmutable $today,
        bool $validForTravel,
        string $message,
    ): array {
        return [
            'status' => $status,
            'severity' => $severity,
            'required_months' =>
                $requiredMonths,
            'required_until' =>
                $requiredUntil,
            'passport_expiry_date' =>
                $passportExpiry,
            'days_remaining' =>
                $passportExpiry
                    ? (int) $today->diffInDays(
                        $passportExpiry,
                        false,
                    )
                    : null,
            'is_valid_for_travel' =>
                $validForTravel,
            'message' => $message,
        ];
    }

    private function nullableDate(
        CarbonInterface|DateTimeInterface|string|null
            $date,
    ): ?CarbonImmutable {
        if ($date === null || $date === '') {
            return null;
        }

        return $this->date($date);
    }

    private function date(
        CarbonInterface|DateTimeInterface|string
            $date,
    ): CarbonImmutable {
        if ($date instanceof CarbonInterface) {
            $value = $date->toDateString();
        } elseif ($date instanceof DateTimeInterface) {
            $value = $date->format('Y-m-d');
        } else {
            $value = $date;
        }

        return CarbonImmutable::parse($value)
            ->startOfDay();
    }
}
