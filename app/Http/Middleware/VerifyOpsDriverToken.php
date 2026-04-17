<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Minimal driver API auth: set OPS_DRIVER_API_TOKEN in env; send X-Ops-Driver-Token header.
 */
final class VerifyOpsDriverToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('ops.driver_api_token');
        if ($expected === null || $expected === '') {
            return response()->json(['message' => 'Driver API not configured.'], 503);
        }

        $sent = $request->header('X-Ops-Driver-Token');
        if (! is_string($sent) || ! hash_equals((string) $expected, $sent)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
