<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    private const TIMEOUT_SECONDS = 30 * 60;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if (!$request->session()->has('last_activity')) {
            $request->session()->put('last_activity', now()->timestamp);
            return $next($request);
        }

        $last = (int) $request->session()->get('last_activity');
        $ahora = now()->timestamp;

        if (($ahora - $last) > self::TIMEOUT_SECONDS) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Sesión expirada por inactividad.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Tu sesión expiró por inactividad. Vuelve a iniciar sesión.');
        }

        // Renovar marca de actividad en cada request autenticada.
        $request->session()->put('last_activity', $ahora);

        return $next($request);
    }
}
