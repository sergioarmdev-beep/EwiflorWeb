<?php

namespace Modules\Ventas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Inventario\Models\Sucursal;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;
use Modules\Precios\Models\TipoCambio;

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
        'fecha_hora'         => 'datetime',
        'fecha_entrega'      => 'date',
        'fecha_cancelacion'  => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_venta';
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
