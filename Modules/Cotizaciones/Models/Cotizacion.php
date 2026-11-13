<?php

namespace Modules\Cotizaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Inventario\Models\Sucursal;
use Modules\Auth\Models\User;
use Modules\Clientes\Models\Cliente;
use Modules\Precios\Models\TipoCambio;

class Cotizacion extends Model
{
    protected $table = 'cotizacion';
    protected $primaryKey = 'id_cotizacion';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'id_sucursal',
        'id_tipo_cambio',
        'fecha_emision',
        'vigencia_dias',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'vigencia_dias' => 'int',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_cotizacion';
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    public function tipoCambio()
    {
        return $this->belongsTo(TipoCambio::class, 'id_tipo_cambio', 'id_tipo_cambio');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class, 'id_cotizacion', 'id_cotizacion');
    }

    /** VIGENTE | VENCIDA | CERRADA — espejo de v_cotizacion_estado. */
    public function condicionTemporal(): string
    {
        if ($this->estado !== 'EMITIDA') {
            return 'CERRADA';
        }
        $vence = $this->fecha_emision->copy()->addDays($this->vigencia_dias);
        return now()->startOfDay()->gt($vence) ? 'VENCIDA' : 'VIGENTE';
    }

    public function totalUsd(): float
    {
        return round((float) $this->detalles->sum(
            fn($d) => $d->cantidad * (float) $d->precio_unitario_usd * (1 - (float) $d->descuento_pct / 100)
        ), 2);
    }

    public function totalBs(): float
    {
        return round($this->totalUsd() * (float) ($this->tipoCambio->valor ?? 0), 2);
    }
}
