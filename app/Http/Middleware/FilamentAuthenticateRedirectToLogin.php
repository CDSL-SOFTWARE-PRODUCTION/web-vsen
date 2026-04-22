<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

/**
 * Filament panels share the main app session; unauthenticated visitors are sent to the central Breeze login.
 */
class FilamentAuthenticateRedirectToLogin extends FilamentAuthenticate
{
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
