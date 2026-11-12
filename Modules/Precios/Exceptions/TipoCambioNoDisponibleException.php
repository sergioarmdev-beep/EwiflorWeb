<?php

namespace Modules\Precios\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando otro módulo pide una conversión y no existe
 * ninguna cotización registrada para el tipo solicitado.
 */
class TipoCambioNoDisponibleException extends RuntimeException
{
    public static function paraTipo(string $tipo): self
    {
        return new self("No hay un tipo de cambio {$tipo} registrado.");
    }
}
