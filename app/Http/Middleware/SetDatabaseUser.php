<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetDatabaseUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->setUser((int) $user->getAuthIdentifier());
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            DB::statement("SELECT set_config('app.id_usuario', '', false)");
        } catch (\Throwable $e) {
            // No crítico: se limpia al reconectar.
        }
    }

    private function setUser(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        DB::statement("SELECT set_config('app.id_usuario', ?, false)", [(string) $id]);
    }
}
