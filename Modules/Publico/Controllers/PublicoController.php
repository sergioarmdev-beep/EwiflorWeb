<?php

namespace Modules\Publico\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalogo\Models\Producto;
use Modules\ServicioTecnico\Services\SolicitudService;

class PublicoController extends Controller
{
    /**
     * Catálogo público sin autenticación.
     */
    public function __construct(private SolicitudService $solicitudes) {}

    public function catalogoPublico()
    {
        $productos = Producto::publicos()
            ->with([
                'marca',
                'imagenes',
                'variantes' => fn ($q) => $q->where('estado', 'ACTIVO'),
            ])
            ->orderBy('nombre')
            ->get();

        // Datos que consume el modal de detalle (se serializan como JSON en la vista).
        $detalle = $productos->mapWithKeys(function (Producto $p) {
            return [$p->id_producto => [
                'id' => $p->id_producto,
                'nombre' => $p->nombre,
                'marca' => $p->marca?->nombre,
                'pais' => $p->marca?->pais_origen,
                'modelo' => $p->modelo,
                'categoria' => $p->categoria,
                'tipo' => $p->tipo_producto,
                'garantia' => (int) $p->garantia_meses,
                'descripcion' => $p->descripcion,
                'imagenes' => $p->imagenes->map(fn ($i) => [
                    'url' => $i->url,
                    'alt' => $i->texto_alternativo ?: $p->nombre,
                ])->values(),
                'variantes' => $p->variantes->map(fn ($v) => [
                    'sku' => $v->sku,
                    'color' => $v->color,
                    'caracteristicas' => $v->caracteristicas,
                ])->values(),
            ]];
        });

        return view('publico::publico', compact('productos', 'detalle'));
    }

    /**
     * Contacta con servicio técnico desde la web pública.
     * Solo clientes existentes (nombre + celular) pueden crear una solicitud.
     */
    public function contactarServicioTecnico(Request $request)
    {
        $validado = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['required', 'string', 'min:7', 'max:20'],
            'mensaje' => ['nullable', 'string', 'min:5', 'max:1000'],
        ]);

        $cliente = $this->solicitudes->buscarCliente($validado['nombre'], $validado['telefono']);

        if ($cliente === null) {
            return back()
                ->withInput()
                ->with('error', 'No se encontró un cliente con ese nombre y número de celular. Asegúrate de estar registrado con compras previas.');
        }

        $solicitud = $this->solicitudes->crearPublica($cliente->id_cliente, [
            'descripcion_falla' => $validado['mensaje'] ?? 'Solicitud de contacto desde la web pública.',
            'id_garantia' => null,
        ]);

        return redirect()
            ->route('serviciotecnico.publico.panel')
            ->with('success', 'Tu solicitud de contacto ha sido registrada. Un técnico la revisará pronto.');
    }
}
