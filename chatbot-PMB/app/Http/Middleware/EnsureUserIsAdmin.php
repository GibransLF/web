<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Akses tidak diizinkan. Hanya Administrator yang dapat mengakses halaman ini.');
            }

            session()->flash('error', 'Akses tidak diizinkan. Halaman tersebut hanya dapat diakses oleh Administrator.');

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
