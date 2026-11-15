<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el precio de lista en USD a cada variante (decisión D-03 del SRS).
 *
 * - La columna es nulable: las variantes existentes no se rompen.
 * - Las variantes que ya se vendieron o cotizaron se inicializan con el
 *   último precio usado, para poder seguir operando sin cargar todo a mano.
 * - Es idempotente: si la columna ya existe, solo completa los precios vacíos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('variante_producto', 'precio_usd')) {
            DB::statement('ALTER TABLE variante_producto ADD COLUMN precio_usd monto_usd');
        }

        // Backfill: precio más reciente registrado en ventas o cotizaciones.
        DB::statement(<<<'SQL'
            UPDATE variante_producto vp
               SET precio_usd = s.precio
              FROM (
                    SELECT DISTINCT ON (u.id_variante) u.id_variante, u.precio
                      FROM (
                            SELECT dv.id_variante,
                                   dv.precio_unitario_usd AS precio,
                                   v.fecha_hora AS momento
                              FROM detalle_venta dv
                              JOIN venta v ON v.id_venta = dv.id_venta
                             UNION ALL
                            SELECT dc.id_variante,
                                   dc.precio_unitario_usd AS precio,
                                   c.fecha_emision::timestamptz AS momento
                              FROM detalle_cotizacion dc
                              JOIN cotizacion c ON c.id_cotizacion = dc.id_cotizacion
                           ) u
                     ORDER BY u.id_variante, u.momento DESC
              ) s
             WHERE vp.id_variante = s.id_variante
               AND vp.precio_usd IS NULL
               AND s.precio > 0
        SQL);
    }

    public function down(): void
    {
        if (Schema::hasColumn('variante_producto', 'precio_usd')) {
            Schema::table('variante_producto', function (Blueprint $table) {
                $table->dropColumn('precio_usd');
            });
        }
    }
};
