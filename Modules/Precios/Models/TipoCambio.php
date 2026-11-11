<?php

namespace Modules\Precios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class TipoCambio extends Model
{
    protected $table = 'tipo_cambio';
    protected $primaryKey = 'id_tipo_cambio';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'tipo',
        'valor',
        'origen',
        'id_usuario',
        'fecha_referencia_api',
    ];

    protected $casts = [
        'fecha'                => 'date',
        'valor'                => 'decimal:4',
        'fecha_registro'       => 'datetime',
        'fecha_referencia_api' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_tipo_cambio';
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function esApi(): bool
    {
        return $this->origen === 'API';
    }

    public function esManual(): bool
    {
        return $this->origen === 'MANUAL';
    }
}
