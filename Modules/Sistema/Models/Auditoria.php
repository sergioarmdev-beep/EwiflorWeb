<?php

namespace Modules\Sistema\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;

class Auditoria extends Model
{
    /**
     * La tabla auditoría es de solo lectura: se llena únicamente mediante
     * triggers de PostgreSQL (fn_auditar_cambio). Nunca se escribe desde Laravel.
     */
    protected $table = 'auditoria';

    protected $primaryKey = 'id_auditoria';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * Solo lectura: no permitir asignación masiva ni escritura.
     */
    protected $guarded = ['*'];

    public $timestamps = false;

    /**
     * Usuario que ejecutó la acción auditada.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
