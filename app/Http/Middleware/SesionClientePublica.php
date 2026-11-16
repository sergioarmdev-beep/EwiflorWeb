<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SesionClientePublica
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('serviciotecnico.cliente_id') === null) {
            return redirect()->route('serviciotecnico.publico.identificar')
                ->with('error', 'Debes identificarte con tu nombre y número de celular para acceder.');
        }

        return $next($request);
    }
}
