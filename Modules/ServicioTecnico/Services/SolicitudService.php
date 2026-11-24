<?php

namespace Modules\ServicioTecnico\Services;

use App\Exceptions\BusinessRuleException;
use App\Support\DbFunction;
use Illuminate\Database\Eloquent\Collection;
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
                'id_sucursal' => $this->sucursalParaPublica($idCliente, $data['id_garantia'] ?? null),
            ]);

            return $solicitud->fresh();
        });
    }

    /**
     * Crea una solicitud desde el panel interno (vendedor/gerente/admin).
     */
    public function crearInterna(int $idCliente, User $registra, array $data): SolicitudServicio
    {
        $idSucursal = $this->sucursalDelUsuario($registra);

        if ($idSucursal === null) {
            throw new BusinessRuleException('Tu usuario no tiene sucursal asignada: no puedes registrar solicitudes.');
        }

        return DB::transaction(function () use ($idCliente, $registra, $data, $idSucursal) {
            $solicitud = SolicitudServicio::create([
                'id_cliente' => $idCliente,
                'id_usuario_registra' => $registra->id_usuario,
                'id_tecnico' => null,
                'descripcion_falla' => $data['descripcion_falla'],
                'fecha_solicitud' => now(),
                'estado' => 'PENDIENTE',
                'origen' => 'INTERNO',
                'id_garantia' => $data['id_garantia'] ?? null,
                'id_sucursal' => $idSucursal,
            ]);

            return $solicitud->fresh();
        });
    }

    /**
     * Sucursal de una solicitud pública (ADR-006): la de la venta de la
     * garantía o, si no hay garantía, la de la última venta del cliente.
     * Sin ventas queda NULL: solo GERENTE podrá verla (fail-closed).
     */
    private function sucursalParaPublica(int $idCliente, ?int $idGarantia): ?int
    {
        if ($idGarantia !== null) {
            $sucursal = DB::table('garantia as g')
                ->join('detalle_venta as dv', 'dv.id_detalle_venta', '=', 'g.id_detalle_venta')
                ->join('venta as v', 'v.id_venta', '=', 'dv.id_venta')
                ->where('g.id_garantia', $idGarantia)
                ->where('v.id_cliente', $idCliente)
                ->value('v.id_sucursal');

            if ($sucursal !== null) {
                return (int) $sucursal;
            }
        }

        $ultima = DB::table('venta')
            ->where('id_cliente', $idCliente)
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id_venta')
            ->value('id_sucursal');

        return $ultima !== null ? (int) $ultima : null;
    }

    public function esGerente(User $user): bool
    {
        return $user->nombre_rol === 'GERENTE';
    }

    public function sucursalDelUsuario(User $user): ?int
    {
        return $user->id_sucursal ? (int) $user->id_sucursal : null;
    }

    public function sucursalDeSolicitud(SolicitudServicio $solicitud): ?int
    {
        return $solicitud->id_sucursal !== null ? (int) $solicitud->id_sucursal : null;
    }

    /**
     * GERENTE opera en todas; el resto solo en su sucursal.
     * Sin sucursal (en cualquiera de los dos lados) → sin acceso.
     */
    public function puedeGestionar(User $user, ?int $idSucursal): bool
    {
        if ($this->esGerente($user)) {
            return true;
        }

        $sucursal = $this->sucursalDelUsuario($user);

        return $idSucursal !== null && $sucursal !== null && $sucursal === $idSucursal;
    }

    /**
     * Aplica el alcance por sucursal a un query de solicitudes.
     * Sin sucursal asignada se fuerza un conjunto vacío (fail-closed).
     */
    public function aplicarAlcanceVisible($query, User $user, string $columna): void
    {
        if ($this->esGerente($user)) {
            return;
        }

        $sucursal = $this->sucursalDelUsuario($user);

        if ($sucursal === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where($columna, $sucursal);
    }

    /**
     * Acepta un encargo pendiente. Bloquea doble asignación con UPDATE condicional.
     * Solo el técnico puede aceptar.
     *
     * Regla de negocio: un técnico solo puede tener una solicitud activa (PROGRAMADA)
     * a la vez. Si ya tiene una, no puede aceptar otra hasta cerrarla.
     */
    public function aceptar(SolicitudServicio $solicitud, User $tecnico): SolicitudServicio
    {
        if ($tecnico->nombre_rol !== 'SERVICIO_TECNICO'
            || ! $this->puedeGestionar($tecnico, $this->sucursalDeSolicitud($solicitud))) {
            throw new BusinessRuleException('No puedes aceptar encargos de otra sucursal.');
        }

        if ($solicitud->estado !== 'PENDIENTE') {
            throw new BusinessRuleException('Esta solicitud ya fue atendida.');
        }

        $yaTieneActiva = DB::table('solicitud_servicio')
            ->where('id_tecnico', $tecnico->id_usuario)
            ->where('estado', 'PROGRAMADA')
            ->exists();

        if ($yaTieneActiva) {
            throw new BusinessRuleException('Ya tienes una solicitud activa. Debes cerrarla antes de aceptar otro encargo.');
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
     *
     * Reglas:
     * - No se puede cerrar si ya está CERRADA o CANCELADA.
     * - Si el usuario es SERVICIO_TECNICO, debe ser el técnico asignado (id_tecnico).
     * - GERENTE y ADMINISTRADOR pueden cerrar cualquier solicitud en estado
     *   PENDIENTE o PROGRAMADA.
     */
    public function cerrar(SolicitudServicio $solicitud, User $user, array $data): SolicitudServicio
    {
        if (in_array($solicitud->estado, ['CERRADA', 'CANCELADA'], true)) {
            throw new BusinessRuleException('Esta solicitud ya está cerrada o cancelada.');
        }

        if (! in_array($solicitud->estado, ['PENDIENTE', 'PROGRAMADA'], true)) {
            throw new BusinessRuleException('No se puede cerrar una solicitud en estado: '.$solicitud->estado);
        }

        if (! $this->puedeGestionar($user, $this->sucursalDeSolicitud($solicitud))) {
            throw new BusinessRuleException('No puedes cerrar solicitudes de otra sucursal.');
        }

        $rol = $user->nombre_rol;
        if ($rol === 'SERVICIO_TECNICO' && $solicitud->id_tecnico !== $user->id_usuario) {
            throw new BusinessRuleException('Solo puedes cerrar solicitudes que te hayan sido asignadas.');
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
     * ¿Puede este usuario consultar el detalle de la solicitud? (ADR-006)
     *
     * GERENTE ve todo; el resto debe compartir la sucursal de la solicitud:
     * ADMINISTRADOR ve todas las de su sucursal, VENDEDOR solo las que él
     * registró y SERVICIO_TECNICO las asignadas a él o PENDIENTE sin asignar.
     * Sin sucursal en el usuario o en la solicitud → sin acceso (fail-closed).
     */
    public function visiblePara(User $user, SolicitudServicio $solicitud): bool
    {
        if (! $this->puedeGestionar($user, $this->sucursalDeSolicitud($solicitud))) {
            return false;
        }

        $rol = $user->nombre_rol;

        if ($rol === 'GERENTE' || $rol === 'ADMINISTRADOR') {
            return true;
        }

        if ($rol === 'SERVICIO_TECNICO') {
            if ($solicitud->id_tecnico !== null) {
                return (int) $solicitud->id_tecnico === (int) $user->id_usuario;
            }

            return $solicitud->estado === 'PENDIENTE';
        }

        if ($rol === 'VENDEDOR') {
            return $solicitud->id_usuario_registra !== null
                && (int) $solicitud->id_usuario_registra === (int) $user->id_usuario;
        }

        return false;
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
     * Devuelve una colección de objetos SolicitudServicio (no arrays) para que
     * las vistas puedan acceder con notación de objeto (->).
     */
    public function pendientesParaTecnico(User $tecnico): Collection
    {
        $query = SolicitudServicio::query()
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])
            ->where(fn ($q) => $q->where('id_tecnico', $tecnico->id_usuario)->orWhereNull('id_tecnico'));

        $this->aplicarAlcanceVisible($query, $tecnico, 'id_sucursal');

        return $query
            ->with('cliente')
            ->orderByDesc('fecha_solicitud')
            ->get();
    }

    /**
     * Todas las solicitudes para la gestión de gerencia (con filtros opcionales).
     */
    public function todas(User $user, array $filtros = [])
    {
        $query = SolicitudServicio::query()
            ->with(['cliente', 'tecnico', 'registra'])
            ->orderByDesc('fecha_solicitud');

        $this->aplicarAlcanceVisible($query, $user, 'id_sucursal');

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['tecnico']) && $filtros['tecnico'] !== '') {
            $query->where('id_tecnico', $filtros['tecnico']);
        }

        if (isset($filtros['en_garantia']) && $filtros['en_garantia'] !== '') {
            if ($filtros['en_garantia'] === '1') {
                $query->whereNotNull('id_garantia');
            } elseif ($filtros['en_garantia'] === '0') {
                $query->whereNull('id_garantia');
            }
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
    public function tecnicos(User $user): array
    {
        $query = User::query()
            ->where('nombre_rol', 'SERVICIO_TECNICO')
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre_completo');

        if (! $this->esGerente($user)) {
            $sucursal = $this->sucursalDelUsuario($user);
            $sucursal === null
                ? $query->whereRaw('1 = 0')
                : $query->where('id_sucursal', $sucursal);
        }

        return $query->get()->toArray();
    }

    /**
     * Cancela una solicitud de servicio (solo GERENTE o ADMINISTRADOR).
     * La lógica transaccional vive en la función PG cancelar_solicitud_servicio().
     */
    public function cancelar(SolicitudServicio $solicitud, User $user, string $motivo): SolicitudServicio
    {
        $rol = $user->nombre_rol;
        if (! in_array($rol, ['GERENTE', 'ADMINISTRADOR'], true)) {
            throw new BusinessRuleException('Solo el Gerente o el Administrador pueden cancelar una solicitud de servicio.');
        }

        if (! $this->puedeGestionar($user, $this->sucursalDeSolicitud($solicitud))) {
            throw new BusinessRuleException('No puedes cancelar solicitudes de otra sucursal.');
        }

        DbFunction::call('cancelar_solicitud_servicio', [
            $solicitud->id_solicitud,
            $user->id_usuario,
            $motivo,
        ]);

        return $solicitud->fresh();
    }
}
