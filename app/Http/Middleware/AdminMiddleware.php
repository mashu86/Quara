<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        if (!$guard->check()) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the admin area.');
        }

        if (!$guard->user()->isAdmin()) {
            $guard->logout();
            return redirect()->route('admin.login')->with('error', 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
