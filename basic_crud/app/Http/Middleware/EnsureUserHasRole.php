<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($roles !== [] && ! $user->hasAnyRole($roles)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.');
        }

        return $next($request);
    }
}
