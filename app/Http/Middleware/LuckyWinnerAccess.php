<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LuckyWinnerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please sign in again to continue your draw.'], 401);
            }

            // Reuse the existing admin login and return to the requested draw screen.
            return redirect()->guest(route('admin.login'));
        }

        abort_unless($request->user()->isAdmin(), 403);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
