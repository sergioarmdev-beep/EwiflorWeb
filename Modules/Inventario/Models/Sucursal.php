<?php

namespace Modules\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\user;

class Sucursal extends Model
{
    protected $table = 'sucursal';

    protected $primaryKey = 'id_sucursal';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'departamento',
        'direccion',
        'telefono',
        'latitud',
        'longitud',
        'estado',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_sucursal';
    }

    public function usuarios()
    {
        return $this->hasMany(user::class, 'id_sucursal', 'id_sucursal');
    }

    public function almacenes()
    {
        return $this->hasMany(Almacen::class, 'id_sucursal', 'id_sucursal');
    }
}
/**
 * @todo Mover a Modules\Inventario\Models\Sucursal cuando se cree el módulo
 *       Inventario (contiene sucursal + almacen + existencia + movimientos).
 *       Al moverlo, actualizar la relación User::sucursal() y
 *       UserController::sucursalesActivas().
 */
