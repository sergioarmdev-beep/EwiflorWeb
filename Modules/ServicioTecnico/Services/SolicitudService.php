<?php

namespace Modules\ServicioTecnico\Services;

use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;
use Modules\Garantias\Models\SolicitudServicio;

class SolicitudService
{
    /**
     * Crea una solicitud desde la web pública (cliente anónimo ya identificado).
     * No requiere id_usuario_registra (queda NULL).
     */
    public function crearPublica(int $idCliente, array $data): SolicitudServicio
    {
        return DB::transaction(function () use ($idCliente, $data) {
            $solicitud = SolicitudServicio::create([
                'id_cliente' => $idCliente,
                'id_usuario_registra' => null,
                'id_tecnico' => null,
                'descripcion_falla' => $data['descripcion_falla'],
                'fecha_solicitud' => now(),
                'estado' => 'PENDIENTE',
                'origen' => 'PUBLICO',
                'id_garantia' => $data['id_garantia'] ?? null,
            ]);

            return $solicitud->fresh();
        });
    }

    /**
     * Crea una solicitud desde el panel interno (vendedor/gerente/admin).
     */
    public function crearInterna(int $idCliente, User $registra, array $data): SolicitudServicio
    {
        return DB::transaction(function () use ($idCliente, $registra, $data) {
            $solicitud = SolicitudServicio::create([
                'id_cliente' => $idCliente,
                'id_usuario_registra' => $registra->id_usuario,
                'id_tecnico' => null,
                'descripcion_falla' => $data['descripcion_falla'],
                'fecha_solicitud' => now(),
                'estado' => 'PENDIENTE',
                'origen' => 'INTERNO',
                'id_garantia' => $data['id_garantia'] ?? null,
            ]);

            return $solicitud->fresh();
        });
    }

    /**
     * Acepta un encargo pendiente. Bloquea doble asignación con UPDATE condicional.
     * Solo el técnico puede aceptar.
     */
    public function aceptar(SolicitudServicio $solicitud, User $tecnico): SolicitudServicio
    {
        if ($solicitud->estado !== 'PENDIENTE') {
            throw new BusinessRuleException('Esta solicitud ya fue atendida.');
        }

        $actualizados = DB::table('solicitud_servicio')
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->where('estado', 'PENDIENTE')
            ->whereNull('id_tecnico')
            ->update([
                'id_tecnico' => $tecnico->id_usuario,
                'estado' => 'PROGRAMADA',
            ]);

        if ($actualizados === 0) {
            throw new BusinessRuleException('Otro técnico ya aceptó este encargo.');
        }

        return $solicitud->fresh();
    }

    /**
     * Cierra una solicitud con resultado, diagnóstico y costo.
     */
    public function cerrar(SolicitudServicio $solicitud, array $data): SolicitudServicio
    {
        if ($solicitud->estado === 'CERRADA') {
            throw new BusinessRuleException('Esta solicitud ya está cerrada.');
        }

        $solicitud->update([
            'estado' => 'CERRADA',
            'resultado' => $data['resultado'],
            'diagnostico' => $data['diagnostico'],
            'costo_servicio_bs' => $data['costo_servicio_bs'] ?? null,
            'fecha_cierre' => now(),
        ]);

        return $solicitud->fresh();
    }

    /**
     * Busca un cliente por nombre y teléfono normalizados.
     * Devuelve null si no existe (bloqueo por diseño).
     */
    public function buscarCliente(string $nombre, string $telefono): ?Cliente
    {
        $nombreNorm = trim(mb_strtolower($nombre));
        $telefonoNorm = preg_replace('/\D+/', '', $telefono);

        return Cliente::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [$nombreNorm])
            ->whereRaw('REGEXP_REPLACE(telefono, \'\D+\', \'\', \'g\') = ?', [$telefonoNorm])
            ->first();
    }

    /**
     * Solicitudes pendientes para el técnico (sin asignar o ya asignadas a él).
     */
    public function pendientesParaTecnico(int $idTecnico): array
    {
        return SolicitudServicio::query()
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->where(fn ($q) => $q->where('id_tecnico', $idTecnico)->orWhereNull('id_tecnico'))
            ->with('cliente')
            ->orderByDesc('fecha_solicitud')
            ->get()
            ->toArray();
    }

    /**
     * Todas las solicitudes para la gestión de gerencia (con filtros opcionales).
     */
    public function todas(array $filtros = [])
    {
        $query = SolicitudServicio::query()
            ->with(['cliente', 'tecnico', 'registra'])
            ->orderByDesc('fecha_solicitud');

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['tecnico']) && $filtros['tecnico'] !== '') {
            $query->where('id_tecnico', $filtros['tecnico']);
        }

        if (isset($filtros['en_garantia']) && $filtros['en_garantia'] === '1') {
            $query->whereNotNull('id_garantia');
        }

        if (isset($filtros['desde']) && $filtros['desde'] !== '') {
            $query->whereDate('fecha_solicitud', '>=', $filtros['desde']);
        }

        if (isset($filtros['hasta']) && $filtros['hasta'] !== '') {
            $query->whereDate('fecha_solicitud', '<=', $filtros['hasta']);
        }

        return $query->paginate(25)->appends($filtros);
    }

    /**
     * Técnicos activos (SERVICIO_TECNICO) para asignar en la gestión.
     */
    public function tecnicos(): array
    {
        return User::query()
            ->where('nombre_rol', 'SERVICIO_TECNICO')
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre_completo')
            ->get()
            ->toArray();
    }
}
