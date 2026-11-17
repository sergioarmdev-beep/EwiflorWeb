<?php

namespace Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Dashboard\Services\AdministradorDashboardService;
use Modules\Dashboard\Services\GerenteDashboardService;
use Modules\Dashboard\Services\SoporteDashboardService;
use Modules\Dashboard\Services\VendedorDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private GerenteDashboardService $gerente,
        private AdministradorDashboardService $administrador,
        private VendedorDashboardService $vendedor,
        private SoporteDashboardService $soporte,
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $rol = $user->nombre_rol;
        $esGerencia = in_array($rol, ['GERENTE', 'ADMINISTRADOR'], true);
        $esVendedor = $rol === 'VENDEDOR';
        $esTecnico = $rol === 'SERVICIO_TECNICO';

        $stats = match (true) {
            $rol === 'GERENTE' => $this->gerente->para($user),
            $rol === 'ADMINISTRADOR' => $this->administrador->para($user),
            $esVendedor => $this->vendedor->para($user),
            $esTecnico => $this->soporte->para($user),
            default => ['rol' => $rol],
        };

        // Pestañas permitidas por rol (según responsabilidades definidas)
        $tabsPermitidas = match (true) {
            $rol === 'GERENTE', $rol === 'ADMINISTRADOR' => [
                'resumen', 'alertas', 'comercial', 'inventario',
                'financiero', 'acciones', 'actividad', 'operativo',
            ],
            $esVendedor => [
                'resumen', 'alertas', 'comercial', 'acciones', 'actividad',
            ],
            $esTecnico => [
                'alertas', 'operativo', 'acciones',
            ],
            default => [],
        };

        return view('dashboard::index', [
            'stats' => $stats,
            'esGerencia' => $esGerencia,
            'esVendedor' => $esVendedor,
            'esTecnico' => $esTecnico,
            'tabsPermitidas' => $tabsPermitidas,
        ]);
    }
}
