<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use RuntimeException;

class GoogleDriveOAuthService
{
    public function getClient(): Client
    {
        $clientJsonPath = $this->clientJsonPath();

        if (! file_exists($clientJsonPath)) {
            throw new RuntimeException('Google OAuth client JSON file nahi mili: ' . $clientJsonPath);
        }

        $client = new Client();
        $client->setAuthConfig($clientJsonPath);
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->setRedirectUri(env('GOOGLE_DRIVE_OAUTH_REDIRECT_URI'));

        return $client;
    }

    public function getAuthUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    public function saveTokenFromCode(string $code): array
    {
        $client = $this->getClient();

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new RuntimeException($token['error_description'] ?? $token['error']);
        }

        $this->saveToken($token);

        return $token;
    }

    public function getAuthorizedClient(): Client
    {
        $client = $this->getClient();
        $tokenPath = $this->tokenJsonPath();

        if (! file_exists($tokenPath)) {
            throw new RuntimeException('Google Drive OAuth connect nahi hua. Pehle /google-drive/oauth/redirect open karo.');
        }

        $token = json_decode((string) file_get_contents($tokenPath), true);

        if (! is_array($token)) {
            throw new RuntimeException('Google Drive OAuth token invalid hai.');
        }

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            if (! $client->getRefreshToken()) {
                throw new RuntimeException('Google Drive refresh token missing hai. Dobara OAuth connect karo.');
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            if (isset($newToken['error'])) {
                throw new RuntimeException($newToken['error_description'] ?? $newToken['error']);
            }

            $newToken['refresh_token'] = $newToken['refresh_token'] ?? $token['refresh_token'] ?? null;

            $this->saveToken($newToken);

            $client->setAccessToken($newToken);
        }

        return $client;
    }

    private function saveToken(array $token): void
    {
        $tokenPath = $this->tokenJsonPath();

        if (! is_dir(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0755, true);
        }

        file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));
    }

    private function clientJsonPath(): string
    {
        return base_path(env('GOOGLE_DRIVE_OAUTH_CLIENT_JSON', 'storage/app/google-drive/oauth-client.json'));
    }

    private function tokenJsonPath(): string
    {
        return base_path(env('GOOGLE_DRIVE_OAUTH_TOKEN_JSON', 'storage/app/google-drive/oauth-token.json'));
    }
}