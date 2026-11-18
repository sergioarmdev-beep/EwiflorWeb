<?php

namespace Modules\Ventas\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;
use Modules\Inventario\Models\Sucursal;
use Modules\Precios\Models\TipoCambio;
use Modules\Sucursales\Support\AlcanceDocumentos;

class Venta extends Model
{
    protected $table = 'venta';

    protected $primaryKey = 'id_venta';

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'id_sucursal',
        'id_cotizacion',
        'id_tipo_cambio',
        'estado',
        'numero_factura',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_entrega' => 'date',
        'fecha_cancelacion' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_venta';
    }

    /**
     * Alcance de consulta por sucursal/autor (ver AlcanceDocumentos):
     *  - GERENTE        → todas las sucursales.
     *  - ADMINISTRADOR  → solo su sucursal.
     *  - VENDEDOR       → solo sus ventas, dentro de su sucursal.
     * Sin usuario o sin sucursal no se ve nada (fail-closed).
     */
    public function scopeVisiblesPara(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if (AlcanceDocumentos::esGerente($user)) {
            return $query;
        }

        $sucursal = AlcanceDocumentos::sucursalDelUsuario($user);
        if ($sucursal === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where($query->qualifyColumn('id_sucursal'), $sucursal);

        if (AlcanceDocumentos::soloSusDocumentos($user)) {
            $query->where($query->qualifyColumn('id_usuario'), $user->id_usuario);
        }

        return $query;
    }

    /** ¿Este usuario puede consultar esta venta concreta? */
    public function puedeVer(?User $user): bool
    {
        return AlcanceDocumentos::puedeVerDocumento(
            $user,
            (int) $this->id_sucursal,
            $this->id_usuario !== null ? (int) $this->id_usuario : null
        );
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function tipoCambio()
    {
        return $this->belongsTo(TipoCambio::class, 'id_tipo_cambio');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta');
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'id_venta');
    }

    public function usuarioCancela()
    {
        return $this->belongsTo(User::class, 'id_usuario_cancela');
    }

    /** Lee totales desde la vista PostgreSQL v_venta_total. */
    public function totales(): ?object
    {
        return DB::table('v_venta_total')->where('id_venta', $this->id_venta)->first();
    }

    public function totalPagadoBs(): float
    {
        return (float) $this->pagos->sum('monto_bs');
    }

    public function saldoBs(): float
    {
        return max(0, round(($this->totales()->total_bs ?? 0) - $this->totalPagadoBs(), 2));
    }
}
