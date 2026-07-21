<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TravelInsurancePrivateFileService
{
    public const POLICY_DIRECTORY =
        'travel-insurance/policies';

    public const RECEIPT_DIRECTORY =
        'travel-insurance/receipts';

    public function syncPolicyDocument(
        TravelInsurancePolicy $policy,
        ?string $previousPath = null,
    ): void {
        $this->syncFileMetadata(
            record: $policy,
            pathField: 'policy_document_path',
            nameField: 'policy_document_name',
            mimeField: 'policy_document_mime',
            sizeField: 'policy_document_size',
            requiredDirectory: self::POLICY_DIRECTORY,
            previousPath: $previousPath,
        );
    }

    public function syncPaymentReceipt(
        TravelInsurancePayment $payment,
        ?string $previousPath = null,
    ): void {
        $this->syncFileMetadata(
            record: $payment,
            pathField: 'receipt_path',
            nameField: 'receipt_name',
            mimeField: 'receipt_mime',
            sizeField: 'receipt_size',
            requiredDirectory: self::RECEIPT_DIRECTORY,
            previousPath: $previousPath,
        );
    }

    public static function deletePolicyDocument(
        ?string $path,
    ): void {
        static::deleteOwnedFile(
            $path,
            self::POLICY_DIRECTORY,
        );
    }

    public static function deletePaymentReceipt(
        ?string $path,
    ): void {
        static::deleteOwnedFile(
            $path,
            self::RECEIPT_DIRECTORY,
        );
    }

    public static function isOwnedPolicyPath(
        ?string $path,
    ): bool {
        return static::isOwnedPath(
            $path,
            self::POLICY_DIRECTORY,
        );
    }

    public static function isOwnedReceiptPath(
        ?string $path,
    ): bool {
        return static::isOwnedPath(
            $path,
            self::RECEIPT_DIRECTORY,
        );
    }

    private function syncFileMetadata(
        object $record,
        string $pathField,
        string $nameField,
        string $mimeField,
        string $sizeField,
        string $requiredDirectory,
        ?string $previousPath,
    ): void {
        $path =
            trim(
                (string) (
                    $record->{$pathField}
                    ?? ''
                ),
            );

        if ($path === '') {
            static::deleteOwnedFile(
                $previousPath,
                $requiredDirectory,
            );

            $record->forceFill(
                [
                    $pathField =>
                        null,

                    $nameField =>
                        null,

                    $mimeField =>
                        null,

                    $sizeField =>
                        null,
                ],
            )->saveQuietly();

            return;
        }

        if (
            ! static::isOwnedPath(
                $path,
                $requiredDirectory,
            )
        ) {
            throw ValidationException::withMessages(
                [
                    $pathField =>
                        'Private insurance file ka storage path invalid hai.',
                ],
            );
        }

        $disk =
            Storage::disk(
                'local',
            );

        if (! $disk->exists($path)) {
            throw ValidationException::withMessages(
                [
                    $pathField =>
                        'Uploaded private insurance file storage mein nahi mili.',
                ],
            );
        }

        if (
            filled(
                $previousPath,
            )
        ) {
            if ($previousPath !== $path) {
                static::deleteOwnedFile(
                    $previousPath,
                    $requiredDirectory,
                );
            }
        }

        $storedName =
            trim(
                (string) (
                    $record->{$nameField}
                    ?? ''
                ),
            );

        if ($storedName === '') {
            $storedName =
                basename(
                    $path,
                );
        }

        $mime =
            $disk->mimeType(
                $path,
            );

        $size =
            $disk->size(
                $path,
            );

        $record->forceFill(
            [
                $nameField =>
                    basename(
                        $storedName,
                    ),

                $mimeField =>
                    is_string(
                        $mime,
                    )
                        ? $mime
                        : 'application/octet-stream',

                $sizeField =>
                    is_int(
                        $size,
                    )
                        ? $size
                        : (int) $size,
            ],
        )->saveQuietly();
    }

    private static function deleteOwnedFile(
        ?string $path,
        string $requiredDirectory,
    ): void {
        if (
            ! static::isOwnedPath(
                $path,
                $requiredDirectory,
            )
        ) {
            return;
        }

        $disk =
            Storage::disk(
                'local',
            );

        if (
            $disk->exists(
                (string) $path,
            )
        ) {
            $disk->delete(
                (string) $path,
            );
        }
    }

    private static function isOwnedPath(
        ?string $path,
        string $requiredDirectory,
    ): bool {
        $normalizedPath =
            trim(
                str_replace(
                    '\\',
                    '/',
                    (string) $path,
                ),
                '/',
            );

        $normalizedDirectory =
            trim(
                str_replace(
                    '\\',
                    '/',
                    $requiredDirectory,
                ),
                '/',
            );

        if ($normalizedPath === '') {
            return false;
        }

        return str_starts_with(
            $normalizedPath,
            $normalizedDirectory.'/',
        );
    }
}