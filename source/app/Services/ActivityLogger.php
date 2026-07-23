<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ActivityLogger
{
    public function created(Model $model): void
    {
        $this->log(
            action: 'Created',
            model: $model,
            oldValues: null,
            newValues: $this->cleanValues($model->getAttributes()),
            description: 'Created ' . $this->moduleName($model) . ': ' . $this->recordTitle($model),
        );
    }

    public function updated(Model $model): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($model->getChanges() as $key => $newValue) {
            if (in_array($key, ['updated_at'], true)) {
                continue;
            }

            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        if (count($newValues) === 0) {
            return;
        }

        $this->log(
            action: 'Updated',
            model: $model,
            oldValues: $this->cleanValues($oldValues),
            newValues: $this->cleanValues($newValues),
            description: 'Updated ' . $this->moduleName($model) . ': ' . $this->recordTitle($model),
        );
    }

    public function deleted(Model $model): void
    {
        $this->log(
            action: 'Deleted',
            model: $model,
            oldValues: $this->cleanValues($model->getOriginal()),
            newValues: null,
            description: 'Deleted ' . $this->moduleName($model) . ': ' . $this->recordTitle($model),
        );
    }

    public function accountingPosting(
        Model $model,
        string $result,
        ?Throwable $exception = null,
    ): void {
        try {
            $user = auth()->user();

            $normalizedResult = strtolower($result);

            $successful =
                $normalizedResult === 'success';

            $skipped =
                $normalizedResult === 'skipped';

            ActivityLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,

                'action' => match (true) {
                    $successful =>
                        'Accounting Posting Success',

                    $skipped =>
                        'Accounting Posting Skipped',

                    default =>
                        'Accounting Posting Failed',
                },

                'module' => 'Accounting Auto Posting',

                'subject_type' => get_class($model),
                'subject_id' => $model->getKey(),

                'record_title' =>
                    $this->recordTitle($model),

                'description' => match (true) {
                    $successful =>
                        'Automatic accounting posting completed for '
                        . $this->moduleName($model)
                        . ': '
                        . $this->recordTitle($model),

                    $skipped =>
                        'Automatic accounting posting skipped for '
                        . $this->moduleName($model)
                        . ': '
                        . $this->recordTitle($model)
                        . ' because no posting was required.',

                    default =>
                        'Automatic accounting posting failed for '
                        . $this->moduleName($model)
                        . ': '
                        . $this->recordTitle($model)
                        . (
                            $exception
                                ? ' - '
                                    . $exception->getMessage()
                                : ''
                        ),
                },

                'old_values' => null,

                'new_values' => [
                    'result' => $normalizedResult,
                    'source_model' => get_class($model),
                    'source_id' => $model->getKey(),
                    'error' => $exception?->getMessage(),
                ],

                'ip_address' => request()?->ip(),
                'user_agent' =>
                    request()?->userAgent(),
            ]);
        } catch (Throwable $loggingException) {
            report($loggingException);
        }
    }
    private function log(
        string $action,
        Model $model,
        ?array $oldValues,
        ?array $newValues,
        string $description,
    ): void {
        try {
            $user = auth()->user();

            ActivityLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,

                'action' => $action,
                'module' => $this->moduleName($model),
                'subject_type' => get_class($model),
                'subject_id' => $model->getKey(),

                'record_title' => $this->recordTitle($model),
                'description' => $description,

                'old_values' => $oldValues,
                'new_values' => $newValues,

                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function moduleName(Model $model): string
    {
        return match (get_class($model)) {
            \App\Models\Country::class => 'Country',
            \App\Models\Airport::class => 'Airport',
            \App\Models\Airline::class => 'Airline',
            \App\Models\VisaType::class => 'Visa Type',
            \App\Models\Supplier::class => 'Supplier',

            \App\Models\Customer::class => 'Customer',
            \App\Models\CustomerDocument::class => 'Customer Document',

            \App\Models\VisaApplication::class => 'Visa Application',
            \App\Models\FlightBooking::class => 'Flight Booking',
            \App\Models\UmrahBooking::class => 'Umrah Booking',
            \App\Models\HotelBooking::class => 'Hotel Booking',

            \App\Models\Payment::class => 'Visa Payment',
            \App\Models\FlightPayment::class => 'Flight Payment',
            \App\Models\UmrahPayment::class => 'Umrah Payment',
            \App\Models\HotelPayment::class => 'Hotel Payment',

            \App\Models\Expense::class => 'Expense',
            \App\Models\ExpenseCategory::class => 'Expense Category',
            \App\Models\Refund::class => 'Refund',
            \App\Models\Invoice::class => 'Invoice',

            \App\Models\CompanySetting::class => 'Company Setting',
            \App\Models\AccountHead::class => 'Chart of Accounts',
            \App\Models\BankAccount::class => 'Bank Account',
            \App\Models\DatabaseBackup::class => 'Database Backup',

            default => class_basename($model),
        };
    }

    private function recordTitle(Model $model): string
    {
        foreach ([
            'application_no',
            'booking_no',
            'invoice_no',
            'refund_no',
            'expense_no',
            'backup_no',
            'name',
            'title',
            'file_name',
            'email',
            'passport_no',
            'reference_no',
            'id',
        ] as $field) {
            if (filled($model->{$field} ?? null)) {
                return (string) $model->{$field};
            }
        }

        return 'Record #' . $model->getKey();
    }

    private function cleanValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $hiddenFields = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($hiddenFields as $field) {
            unset($values[$field]);
        }

        foreach (
            [
                'account_number',
                'iban',
            ]
            as $field
        ) {
            if (
                array_key_exists($field, $values)
                && filled($values[$field])
            ) {
                $values[$field] =
                    $this->redactSensitiveIdentifier(
                        $values[$field],
                    );
            }
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }

        return $values;
    }

    private function redactSensitiveIdentifier(
        mixed $value,
    ): string {
        $normalized =
            trim(
                (string) $value,
            );

        if ($normalized === '') {
            return '[REDACTED]';
        }

        $lastFour =
            strlen($normalized) > 4
                ? substr($normalized, -4)
                : '';

        return $lastFour === ''
            ? '[REDACTED]'
            : '[REDACTED:'.$lastFour.']';
    }
}