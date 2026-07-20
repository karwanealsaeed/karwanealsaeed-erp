<?php

namespace App\Observers;

use App\Models\VisaApplication;
use App\Services\PassportValidityService;

class VisaApplicationObserver
{
    public function __construct(
        private readonly PassportValidityService
            $passportValidity,
    ) {
    }

    public function saving(
        VisaApplication $application,
    ): void {
        $requiredMonths = (int) (
            $application
                ->required_passport_validity_months
            ?? PassportValidityService
                ::DEFAULT_VALIDITY_MONTHS
        );

        $requiredMonths = max(
            0,
            min(24, $requiredMonths),
        );

        $result = $this->passportValidity
            ->evaluate(
                passportExpiry:
                    $application
                        ->passport_expiry_date,
                travelDate:
                    $application->travel_date,
                returnDate:
                    $application->return_date,
                requiredMonths:
                    $requiredMonths,
            );

        $application
            ->required_passport_validity_months =
                $requiredMonths;

        $application->passport_required_until =
            $result['required_until'];

        $application->passport_validity_status =
            $result['status'];

        $application
            ->passport_validity_checked_at =
                now();
    }
}
