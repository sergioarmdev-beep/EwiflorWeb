<?php

namespace Modules\Sucursales\Support;

/**
 * Catálogos de valores permitidos. Deben replicar los CHECK del esquema
 * PostgreSQL (database/sql/v5-MVP.sql):
 *
 *   sucursal.departamento IN ('LA_PAZ','SANTA_CRUZ','COCHABAMBA','ORURO','POTOSI',
 *                             'TARIJA','CHUQUISACA','BENI','PANDO')
 *   almacen.tamano IN ('GRANDE','PEQUENO')
 */
final class Catalogos
{
    public const DEPARTAMENTOS = [
        'LA_PAZ', 'SANTA_CRUZ', 'COCHABAMBA', 'ORURO', 'POTOSI',
        'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO',
    ];

    public const TAMANOS_ALMACEN = ['GRANDE', 'PEQUENO'];
}
