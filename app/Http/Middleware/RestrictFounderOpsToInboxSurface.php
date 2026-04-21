<?php

namespace App\Http\Middleware;

use App\Filament\Ops\Pages\FounderInbox;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Founder role uses Ops only as a thin surface (inbox + digest). Block deep panel URLs.
 */
final class RestrictFounderOpsToInboxSurface
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || $user->role !== 'Founder') {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        return redirect()->to(FounderInbox::getUrl(panel: 'ops'));
    }

    private function isAllowed(Request $request): bool
    {
        $name = $request->route()?->getName() ?? '';

        if (str_starts_with($name, 'filament.ops.pages.founder-inbox')) {
            return true;
        }

        if (str_starts_with($name, 'filament.ops.auth.')) {
            return true;
        }

        if ($name === 'language.switch') {
            return true;
        }

        if ($name === 'ops.founder.digest-export') {
            return true;
        }

        $path = $request->path();

        if (str_starts_with($path, 'livewire/')) {
            return true;
        }

        return false;
    }
}
