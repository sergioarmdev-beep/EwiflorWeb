<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Auth\Requests\StoreUserRequest;
use Modules\Auth\Requests\UpdateUserRequest;

use Modules\Inventario\Models\Sucursal;

class UserController extends Controller
{
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

        $usuarios = $query->orderBy('nombre_completo')->paginate(15)->withQueryString();

        return view('auth::usuarios.index', [
            'usuarios'   => $usuarios,
            'sucursales' => $this->sucursalesActivas(),
        ]);
    }

    public function create()
    {
        return view('auth::usuarios.create', [
            'usuario'    => new User(['estado' => 'ACTIVO', 'porcentaje_comision_pct' => 0]),
            'sucursales' => $this->sucursalesActivas(),
            'roles'      => $this->rolesPermitidos(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['contrasena_hash'] = Hash::make($data['password']);
        unset($data['password'], $data['password_confirmation']);

        $usuario = User::create($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre_completo} creado correctamente.");
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
        $roles = [
            'ADMINISTRADOR'    => 'Administrador',
            'VENDEDOR'         => 'Vendedor',
            'SERVICIO_TECNICO' => 'Servicio técnico',
        ];

        if (request()->user()?->esGerente()) {
            $roles = ['GERENTE' => 'Gerente'] + $roles;
        }

        return $roles;
    }

    private function sucursalesActivas()
    {
        return Sucursal::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get(['id_sucursal', 'nombre']);
    }
}
