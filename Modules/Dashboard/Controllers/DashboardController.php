<?php

namespace Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Dashboard\Services\AdministradorDashboardService;
use Modules\Dashboard\Services\GerenteDashboardService;
use Modules\Dashboard\Services\SoporteDashboardService;
use Modules\Dashboard\Services\VendedorDashboardService;
use Modules\Inventario\Models\Sucursal;

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

        // --- Filtros de la comparativa de ventas (solo gerencia) ---
        $mes1 = $request->input('mes1', now()->format('Y-m'));
        $mes2 = $request->input('mes2', now()->subMonth()->format('Y-m'));
        $meses = $this->mesesDisponibles();

        $sucursales = collect();
        $idSucursalSeleccionada = null;
        $ventasLineas = ['error' => false, 'mes1' => [], 'mes2' => []];

        if ($esGerencia) {
            if ($rol === 'GERENTE') {
                $sucursales = Sucursal::where('estado', 'ACTIVO')->orderBy('nombre')->get();
                $idSucursalSeleccionada = $request->input('id_sucursal') ?: null;
                $idSucursalConsulta = $idSucursalSeleccionada; // null = sumatoria de todas
            } else {
                // ADMINISTRADOR: solo su sucursal asignada
                $idSucursalConsulta = $user->id_sucursal ?? null;
                if ($user->sucursal) {
                    $sucursales = collect([$user->sucursal]);
                }
                $idSucursalSeleccionada = $idSucursalConsulta;
            }

            $puedeConsultar = ! ($rol === 'ADMINISTRADOR' && ! $idSucursalConsulta);

            if ($puedeConsultar) {
                try {
                    $ventasLineas = ($rol === 'GERENTE' ? $this->gerente : $this->administrador)
                        ->ventasLineasComparativo($mes1, $mes2, $idSucursalConsulta);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo cargar la comparativa inicial de ventas (dashboard)', [
                        'usuario' => $user->id_usuario,
                        'rol' => $rol,
                        'mes1' => $mes1, 'mes2' => $mes2,
                        'id_sucursal' => $idSucursalConsulta,
                        'exception' => $e->getMessage(),
                    ]);
                    $ventasLineas = [
                        'error' => true,
                        'mes1' => ['etiqueta' => $mes1, 'valor' => $mes1, 'dias' => [], 'total' => 0],
                        'mes2' => ['etiqueta' => $mes2, 'valor' => $mes2, 'dias' => [], 'total' => 0],
                    ];
                }
            } else {
                $ventasLineas = [
                    'error' => true,
                    'mensaje' => 'Tu usuario de administrador no tiene una sucursal asignada.',
                    'mes1' => ['etiqueta' => $mes1, 'valor' => $mes1, 'dias' => [], 'total' => 0],
                    'mes2' => ['etiqueta' => $mes2, 'valor' => $mes2, 'dias' => [], 'total' => 0],
                ];
            }
        }

        $stats['ventas_lineas'] = $ventasLineas;

        // Pestaña de comparativa de ventas (solo gerencia)
        if ($esGerencia) {
            $tabsPermitidas[] = 'comparativo';
        }

        return view('dashboard::index', [
            'stats' => $stats,
            'esGerencia' => $esGerencia,
            'esVendedor' => $esVendedor,
            'esTecnico' => $esTecnico,
            'tabsPermitidas' => $tabsPermitidas,
            'meses' => $meses,
            'mes1' => $mes1,
            'mes2' => $mes2,
            'sucursales' => $sucursales,
            'idSucursalSeleccionada' => $idSucursalSeleccionada,
        ]);
    }

    /** Comparativa de ventas diarias entre dos meses (AJAX). */
    public function lineasVentas(Request $request)
    {
        $user = $request->user();
        $rol = $user->nombre_rol;

        if (! in_array($rol, ['GERENTE', 'ADMINISTRADOR'], true)) {
            return response()->json(['error' => true, 'message' => 'No tienes permiso para ver esta estadística.'], 403);
        }

        $mes1 = $request->string('mes1');
        $mes2 = $request->string('mes2');

        if (! $mes1 || ! $mes2 || ! preg_match('/^\d{4}-\d{2}$/', $mes1) || ! preg_match('/^\d{4}-\d{2}$/', $mes2)) {
            return response()->json(['error' => true, 'message' => 'Selecciona dos meses válidos (formato AAAA-MM).'], 422);
        }

        $idSucursal = null;
        if ($rol === 'ADMINISTRADOR') {
            $idSucursal = $user->id_sucursal ?? null;
            if (! $idSucursal) {
                return response()->json(['error' => true, 'message' => 'Tu usuario de administrador no tiene una sucursal asignada.'], 422);
            }
        } elseif ($rol === 'GERENTE') {
            $idSucursalRaw = $request->input('id_sucursal');
            $idSucursal = ($idSucursalRaw === null || $idSucursalRaw === '') ? null : (int) $idSucursalRaw; // null = todas las sucursales
        }

        try {
            $datos = ($rol === 'GERENTE' ? $this->gerente : $this->administrador)
                ->ventasLineasComparativo($mes1, $mes2, $idSucursal);

            return response()->json($datos);
        } catch (\Throwable $e) {
            Log::error('Error cargando comparativa de ventas (dashboard)', [
                'usuario' => $user->id_usuario,
                'rol' => $rol,
                'mes1' => $mes1, 'mes2' => $mes2,
                'id_sucursal' => $idSucursal,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'No se pudo cargar la comparativa de ventas. Inténtalo de nuevo más tarde.',
            ], 500);
        }
    }

    private function mesesDisponibles(): array
    {
        $nombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $meses[] = [
                'valor' => $m->format('Y-m'),
                'etiqueta' => $nombres[(int) $m->month].' '.$m->year,
            ];
        }

        return $meses;
    }
}
