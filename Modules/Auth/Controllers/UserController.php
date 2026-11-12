<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\SolicitudCuenta;
use Modules\Auth\Models\User;
use Modules\Auth\Requests\StoreUserRequest;
use Modules\Auth\Requests\UpdateUserRequest;
use Modules\Auth\Support\GeneradorContrasena;

use Modules\Inventario\Models\Sucursal;

class UserController extends Controller
{
    /** Columnas por las que se puede ordenar el listado (lista blanca). */
    private const ORDENABLES = [
        'nombre_completo',
        'nombre_usuario',
        'correo',
        'nombre_rol',
        'sucursal',
        'porcentaje_comision_pct',
        'estado',
    ];

    public function index(Request $request)
    {
        $query = User::query()->with('sucursal');

        if ($buscar = trim((string) $request->input('buscar'))) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_completo', 'ILIKE', "%{$buscar}%")
                    ->orWhere('nombre_usuario', 'ILIKE', "%{$buscar}%")
                    ->orWhere('correo', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($rol = $request->input('rol')) {
            $query->where('nombre_rol', $rol);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($idSucursal = $request->input('id_sucursal')) {
            $query->where('id_sucursal', $idSucursal);
        }

        // Orden por columna (clic en el encabezado de la tabla)
        $orden = in_array($request->input('orden'), self::ORDENABLES, true) ? $request->input('orden') : 'nombre_completo';
        $dir   = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        if ($orden === 'sucursal') {
            $sucursal = new Sucursal();
            $query->orderBy(
                DB::table($sucursal->getTable())
                    ->select('nombre')
                    ->whereColumn($sucursal->getTable() . '.' . $sucursal->getKeyName(), 'usuario.id_sucursal'),
                $dir
            );
        } else {
            $query->orderBy($orden, $dir);
        }
        if ($orden !== 'nombre_completo') {
            $query->orderBy('nombre_completo');
        }
        $query->orderBy('id_usuario');

        $usuarios = $query->paginate(15)->withQueryString();

        return view('auth::usuarios.index', [
            'usuarios'   => $usuarios,
            'sucursales' => $this->sucursalesActivas(),
            'orden'      => $orden,
            'dir'        => $dir,
            'resumen'    => $this->resumen(),
        ]);
    }

    public function create(Request $request)
    {
        $usuario = new User(['estado' => 'ACTIVO', 'porcentaje_comision_pct' => 0]);

        // Viene desde "Solicitudes de cuenta": precargamos los datos de la solicitud pendiente.
        $solicitud = null;
        if ($idSolicitud = $request->integer('solicitud')) {
            $solicitud = SolicitudCuenta::query()
                ->whereKey($idSolicitud)
                ->where('estado', SolicitudCuenta::PENDIENTE)
                ->first();

            if ($solicitud) {
                $usuario->nombre_completo = $solicitud->nombre_completo;
                $usuario->correo = $solicitud->correo;
            }
        }

        return view('auth::usuarios.create', [
            'usuario'    => $usuario,
            'solicitud'  => $solicitud,
            'sucursales' => $this->sucursalesActivas(),
            'roles'      => $this->rolesPermitidos(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['contrasena_hash'] = Hash::make($data['password']);
        $idSolicitud = $data['id_solicitud'] ?? null;
        unset($data['password'], $data['password_confirmation'], $data['id_solicitud']);

        $usuario = DB::transaction(function () use ($data, $idSolicitud, $request) {
            $usuario = User::create($data);

            if ($idSolicitud) {
                SolicitudCuenta::whereKey($idSolicitud)->update([
                    'estado'             => SolicitudCuenta::APROBADA,
                    'id_usuario_revisor' => $request->user()->id_usuario,
                    'fecha_revision'     => now(),
                    'id_usuario_creado'  => $usuario->id_usuario,
                ]);
            }

            return $usuario;
        });

        $mensaje = "Usuario {$usuario->nombre_completo} creado correctamente.";
        if ($idSolicitud) {
            $mensaje .= ' La solicitud de cuenta quedó marcada como aprobada.';
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', $mensaje);
    }

    public function edit(User $usuario)
    {
        $this->autorizarGestionDe($usuario);

        return view('auth::usuarios.edit', [
            'usuario'    => $usuario,
            'sucursales' => $this->sucursalesActivas(),
            'roles'      => $this->rolesPermitidos(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $usuario)
    {
        $this->autorizarGestionDe($usuario);

        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['contrasena_hash'] = Hash::make($data['password']);
        }
        unset($data['password'], $data['password_confirmation']);

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre_completo} actualizado correctamente.");
    }

    public function toggleEstado(Request $request, User $usuario)
    {
        $this->autorizarGestionDe($usuario);

        if ($usuario->id_usuario === $request->user()->id_usuario) {
            return back()->with('error', 'No puedes cambiar el estado de tu propia cuenta.');
        }

        $usuario->estado = $usuario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $usuario->save();

        $mensaje = $usuario->estado === 'ACTIVO'
            ? "Usuario {$usuario->nombre_completo} activado."
            : "Usuario {$usuario->nombre_completo} desactivado. Su historial se conserva.";

        return back()->with('success', $mensaje);
    }

    /**
     * Genera una contraseña temporal, la guarda hasheada y obliga al usuario a cambiarla
     * en su próximo ingreso. La clave en claro se muestra UNA sola vez (flash de sesión).
     */
    public function resetPassword(Request $request, User $usuario)
    {
        $this->autorizarGestionDe($usuario);

        if ($usuario->id_usuario === $request->user()->id_usuario) {
            return back()->with('error', 'Para cambiar tu propia contraseña usa «Mi perfil».');
        }

        $temporal = GeneradorContrasena::generar(10, false);

        $usuario->forceFill([
            'contrasena_hash'         => Hash::make($temporal),
            'debe_cambiar_contrasena' => true,
        ])->save();

        return redirect()
            ->route('usuarios.index', $request->only(['buscar', 'rol', 'estado', 'id_sucursal', 'orden', 'dir', 'page']))
            ->with('password_temporal', [
                'nombre'         => $usuario->nombre_completo,
                'nombre_usuario' => $usuario->nombre_usuario,
                'password'       => $temporal,
            ]);
    }

    /**
     * Un Administrador no puede tocar cuentas de Gerente ni ascender a nadie a Gerente.
     */
    private function autorizarGestionDe(User $usuario): void
    {
        $actor = request()->user();

        if ($actor->esGerente()) {
            return;
        }

        if ($usuario->esGerente()) {
            abort(403, 'Solo un Gerente puede gestionar cuentas de Gerente.');
        }
    }

    private function rolesPermitidos(): array
    {
        $etiquetas = [
            'GERENTE'          => 'Gerente',
            'ADMINISTRADOR'    => 'Administrador',
            'VENDEDOR'         => 'Vendedor',
            'SERVICIO_TECNICO' => 'Servicio técnico',
        ];

        $permitidos = request()->user()?->rolesAsignables() ?? [];

        return array_intersect_key($etiquetas, array_flip($permitidos));
    }

    /** Totales para las tarjetas de resumen (sobre todos los usuarios, sin filtros). */
    private function resumen(): array
    {
        $tot = User::query()
            ->selectRaw("COUNT(*) AS total,
                SUM(CASE WHEN estado = 'ACTIVO' THEN 1 ELSE 0 END) AS activos,
                SUM(CASE WHEN estado = 'INACTIVO' THEN 1 ELSE 0 END) AS inactivos")
            ->first();

        return [
            'total'      => (int) ($tot->total ?? 0),
            'activos'    => (int) ($tot->activos ?? 0),
            'inactivos'  => (int) ($tot->inactivos ?? 0),
            'por_rol'    => User::query()->selectRaw('nombre_rol, COUNT(*) AS c')->groupBy('nombre_rol')->pluck('c', 'nombre_rol')->all(),
            'solicitudes' => SolicitudCuenta::where('estado', SolicitudCuenta::PENDIENTE)->count(),
        ];
    }

    private function sucursalesActivas()
    {
        return Sucursal::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get(['id_sucursal', 'nombre']);
    }
}
