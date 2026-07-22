<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\GoogleDriveReconnectRequiredException;
use App\Services\GoogleDriveOAuthService;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

final class GoogleDriveOAuthSafetyTest extends TestCase
{
    private string $temporaryDirectory;

    private string|false $previousTokenPath;

    private string|false $previousClientPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousTokenPath =
            getenv(
                'GOOGLE_DRIVE_OAUTH_TOKEN_JSON',
            );

        $this->previousClientPath =
            getenv(
                'GOOGLE_DRIVE_OAUTH_CLIENT_JSON',
            );

        $this->temporaryDirectory =
            storage_path(
                'app/private/testing/google-drive-oauth-'
                .bin2hex(random_bytes(8)),
            );

        mkdir(
            $this->temporaryDirectory,
            0775,
            true,
        );

        $relativeDirectory =
            str_replace(
                base_path().DIRECTORY_SEPARATOR,
                '',
                $this->temporaryDirectory,
            );

        $relativeDirectory =
            str_replace(
                '\\',
                '/',
                $relativeDirectory,
            );

        $this->setEnvironmentValue(
            'GOOGLE_DRIVE_OAUTH_TOKEN_JSON',
            $relativeDirectory.'/oauth-token.json',
        );

        $this->setEnvironmentValue(
            'GOOGLE_DRIVE_OAUTH_CLIENT_JSON',
            $relativeDirectory.'/oauth-client.json',
        );
    }

    protected function tearDown(): void
    {
        foreach (
            [
                $this->tokenPath(),
                $this->clientPath(),
            ]
            as $path
        ) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        if (is_dir($this->temporaryDirectory)) {
            rmdir($this->temporaryDirectory);
        }

        $this->restoreEnvironmentValue(
            'GOOGLE_DRIVE_OAUTH_TOKEN_JSON',
            $this->previousTokenPath,
        );

        $this->restoreEnvironmentValue(
            'GOOGLE_DRIVE_OAUTH_CLIENT_JSON',
            $this->previousClientPath,
        );

        parent::tearDown();
    }

    public function test_invalid_grant_requires_reconnect(): void
    {
        $this->assertTrue(
            GoogleDriveReconnectRequiredException::responseRequiresReconnect(
                [
                    'error' => 'invalid_grant',
                    'error_description' =>
                        'Token has been expired or revoked.',
                ],
            ),
        );
    }

    public function test_transient_provider_failure_is_not_revocation(): void
    {
        $this->assertFalse(
            GoogleDriveReconnectRequiredException::responseRequiresReconnect(
                [
                    'error' =>
                        'temporarily_unavailable',
                    'error_description' =>
                        'Provider temporarily unavailable.',
                ],
            ),
        );
    }

    public function test_confirmed_revocation_removes_only_oauth_token(): void
    {
        file_put_contents(
            $this->tokenPath(),
            '{"refresh_token":"private-test-token"}',
        );

        file_put_contents(
            $this->clientPath(),
            '{"installed":{"client_id":"safe-test-client"}}',
        );

        $exception =
            $this->invokeOAuthFailure(
                [
                    'error' => 'invalid_grant',
                    'error_description' =>
                        'Token has been expired or revoked.',
                ],
            );

        $this->assertInstanceOf(
            GoogleDriveReconnectRequiredException::class,
            $exception,
        );

        $this->assertFileDoesNotExist(
            $this->tokenPath(),
        );

        $this->assertFileExists(
            $this->clientPath(),
        );
    }

    public function test_transient_failure_keeps_token_and_returns_generic_error(): void
    {
        file_put_contents(
            $this->tokenPath(),
            '{"refresh_token":"private-test-token"}',
        );

        $exception =
            $this->invokeOAuthFailure(
                [
                    'error' =>
                        'temporarily_unavailable',
                    'error_description' =>
                        'Internal provider detail must stay private.',
                ],
            );

        $this->assertInstanceOf(
            RuntimeException::class,
            $exception,
        );

        $this->assertNotInstanceOf(
            GoogleDriveReconnectRequiredException::class,
            $exception,
        );

        $this->assertSame(
            'Google Drive authorization failed. Reconnect Google Drive and try again.',
            $exception->getMessage(),
        );

        $this->assertStringNotContainsString(
            'Internal provider detail',
            $exception->getMessage(),
        );

        $this->assertFileExists(
            $this->tokenPath(),
        );
    }

    public function test_service_and_controller_do_not_expose_raw_provider_errors(): void
    {
        $serviceSource =
            file_get_contents(
                app_path(
                    'Services/GoogleDriveOAuthService.php',
                ),
            );

        $controllerSource =
            file_get_contents(
                app_path(
                    'Http/Controllers/GoogleDriveOAuthController.php',
                ),
            );

        $this->assertIsString($serviceSource);
        $this->assertIsString($controllerSource);

        $this->assertDoesNotMatchRegularExpression(
            '/throw\s+new\s+RuntimeException\s*\([^;]*error_description/s',
            $serviceSource,
        );

        $this->assertStringContainsString(
            'deleteStoredOAuthToken',
            $serviceSource,
        );

        $this->assertStringContainsString(
            'tokenJsonPath',
            $serviceSource,
        );

        $this->assertStringNotContainsString(
            '->getMessage()',
            $controllerSource,
        );

        $this->assertStringNotContainsString(
            "\$request->get('error')",
            $controllerSource,
        );
    }

    private function invokeOAuthFailure(
        array $response,
    ): RuntimeException {
        $service =
            new GoogleDriveOAuthService();

        $reflection =
            new ReflectionClass(
                $service,
            );

        $method =
            $reflection->getMethod(
                'oauthFailureException',
            );

        $method->setAccessible(true);

        $result =
            $method->invoke(
                $service,
                $response,
            );

        $this->assertInstanceOf(
            RuntimeException::class,
            $result,
        );

        return $result;
    }

    private function tokenPath(): string
    {
        return $this->temporaryDirectory
            .DIRECTORY_SEPARATOR
            .'oauth-token.json';
    }

    private function clientPath(): string
    {
        return $this->temporaryDirectory
            .DIRECTORY_SEPARATOR
            .'oauth-client.json';
    }

    private function setEnvironmentValue(
        string $key,
        string $value,
    ): void {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private function restoreEnvironmentValue(
        string $key,
        string|false $value,
    ): void {
        if ($value === false) {
            putenv($key);
            unset(
                $_ENV[$key],
                $_SERVER[$key],
            );

            return;
        }

        $this->setEnvironmentValue(
            $key,
            $value,
        );
    }
}
