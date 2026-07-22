<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleDriveReconnectRequiredException;

use App\Services\GoogleDriveOAuthService;
use Illuminate\Http\Request;
use Throwable;

class GoogleDriveOAuthController extends Controller
{
    public function redirect(GoogleDriveOAuthService $googleDriveOAuthService)
    {
        abort_unless(auth()->user()?->isAdmin() ?? false, 403);

        return redirect()->away($googleDriveOAuthService->getAuthUrl());
    }

    public function callback(Request $request, GoogleDriveOAuthService $googleDriveOAuthService)
    {
        abort_unless(auth()->user()?->isAdmin() ?? false, 403);

        if ($request->filled('error')) {
            return response('Google Drive authorization was not completed.', 400);
        }

        if (! $request->filled('code')) {
            return response('Google Drive OAuth code missing.', 400);
        }

        try {
            $googleDriveOAuthService->saveTokenFromCode((string) $request->get('code'));

            return response('<h2>Google Drive connected successfully.</h2><p>Ab aap backup run kar sakte hain.</p>');
        } catch (GoogleDriveReconnectRequiredException $exception) {
            report($exception);

            return response(
                '<h2>Google Drive reconnect required.</h2><p>Reconnect Google Drive from System Health and try again.</p>',
                409,
            );
        } catch (Throwable $exception) {
            report($exception);

            return response('<h2>Google Drive connection failed.</h2><p>Reconnect Google Drive and try again.</p>', 500);
        }
    }
}