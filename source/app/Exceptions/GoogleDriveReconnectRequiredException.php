<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class GoogleDriveReconnectRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Google Drive authorization has expired or is unavailable. Reconnect Google Drive.',
        );
    }

    /**
     * @param array<string, mixed> $response
     */
    public static function responseRequiresReconnect(array $response): bool
    {
        $error =
            strtolower(
                trim(
                    (string) ($response['error'] ?? ''),
                ),
            );

        $description =
            strtolower(
                trim(
                    (string) ($response['error_description'] ?? ''),
                ),
            );

        if (
            in_array(
                $error,
                [
                    'invalid_grant',
                    'invalid_token',
                    'unauthorized_client',
                ],
                true,
            )
        ) {
            return true;
        }

        foreach (
            [
                'expired',
                'revoked',
                'invalid grant',
                'invalid token',
                'reauthorize',
                're-authorize',
            ]
            as $signal
        ) {
            if (str_contains($description, $signal)) {
                return true;
            }
        }

        return false;
    }
}
