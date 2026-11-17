<?php

namespace Modules\Catalogo\Services;

use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Catalogo\Models\Producto;
use Modules\Catalogo\Models\ProductoImagen;

/**
 * Operaciones de negocio sobre productos que reutilizan otros módulos:
 * combos (Promociones) usa la creación de imágenes y variantes; el
 * controlador de Catálogo delega aquí en lugar de duplicar la lógica.
 */
class ProductoService
{
    /**
     * Stock por producto, calculado desde `existencia` (que se guarda por variante y almacén).
     * - total:      suma de cantidad_total
     * - disponible: total - reservado (es lo que se puede vender; es el mismo criterio
     *               que usa la vista v_reporte_existencias para la alerta REPONER)
     */
    public const SQL_STOCK_TOTAL = '(select COALESCE(SUM(e.cantidad_total), 0) from existencia e join variante_producto vp on vp.id_variante = e.id_variante where vp.id_producto = producto.id_producto)';

    public const SQL_STOCK_DISPONIBLE = '(select COALESCE(SUM(e.cantidad_total - e.cantidad_reservada), 0) from existencia e join variante_producto vp on vp.id_variante = e.id_variante where vp.id_producto = producto.id_producto)';

    /** Roles que pueden fijar precios de lista (RN-004). */
    public const ROLES_EDITAN_PRECIO = ['GERENTE', 'ADMINISTRADOR'];

    /**
     * Guarda las imágenes subidas. La primera pasa a ser la principal solo si
     * el producto todavía no tiene una. Devuelve las rutas guardadas.
     *
     * @param  array<int, UploadedFile>  $archivos
     * @return string[]
     */
    public function guardarImagenes(Producto $producto, array $archivos): array
    {
        if (! $archivos) {
            return [];
        }

        // Se consulta el modelo directo (sin el orderBy de la relación) porque
        // PostgreSQL no permite ORDER BY junto a una función de agregado.
        $existentes = ProductoImagen::where('id_producto', $producto->id_producto);
        $tienePrincipal = (clone $existentes)->where('es_principal', true)->exists();
        $maxOrden = (clone $existentes)->max('orden');
        $orden = $maxOrden === null ? 0 : ((int) $maxOrden + 1);
        $rutas = [];

        foreach (array_values($archivos) as $i => $archivo) {
            $ruta = $archivo->store('productos', 'public');
            $rutas[] = $ruta;

            $producto->imagenes()->create([
                'ruta_imagen' => $ruta,
                'es_principal' => ! $tienePrincipal && $i === 0,
                'orden' => $orden + $i,
            ]);
        }

        return $rutas;
    }

    /**
     * Guarda los archivos de documentación subidos (.txt, .doc, .docx).
     * Devuelve las rutas guardadas.
     *
     * @param  array<int, UploadedFile>  $archivos
     * @return string[]
     */
    public function guardarDocumentos(Producto $producto, array $archivos): array
    {
        if (! $archivos) {
            return [];
        }

        $rutas = [];

        foreach (array_values($archivos) as $archivo) {
            $ruta = $archivo->store('documentos-productos', 'public');
            $rutas[] = $ruta;

            $producto->documentos()->create([
                'id_producto' => $producto->id_producto,
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta_archivo' => $ruta,
                'extension' => $archivo->getClientOriginalExtension(),
                'fecha_subida' => now(),
            ]);
        }

        return $rutas;
    }

    /** Deja exactamente una imagen principal (la elegida, o la primera si no hay). */
    public function asegurarPrincipal(Producto $producto, ?int $idElegida): void
    {
        $imagenes = $producto->imagenes()->get();
        if ($imagenes->isEmpty()) {
            return;
        }

        $principal = $idElegida ? $imagenes->firstWhere('id_imagen', $idElegida) : null;
        $principal ??= $imagenes->firstWhere('es_principal', true) ?? $imagenes->first();

        ProductoImagen::where('id_producto', $producto->id_producto)
            ->where('id_imagen', '!=', $principal->id_imagen)
            ->where('es_principal', true)
            ->update(['es_principal' => false]);

        if (! $principal->es_principal) {
            $principal->update(['es_principal' => true]);
        }
    }

    /**
     * Sincroniza las variantes con lo enviado en el formulario:
     * actualiza las existentes, crea las nuevas y retira las que se quitaron.
     * Si una variante quitada tiene movimientos asociados (FK), se desactiva en vez de borrarse.
     */
    public function sincronizarVariantes(Producto $producto, array $variantes, bool $puedeEditarPrecio): void
    {
        $conservadas = [];

        foreach ($variantes as $v) {
            $datos = [
                'sku' => trim($v['sku']),
                'color' => ($v['color'] ?? null) ?: null,
                'caracteristicas' => ($v['caracteristicas'] ?? null) ?: null,
                'estado' => $v['estado'] ?? 'ACTIVO',
            ];

            if ($puedeEditarPrecio && array_key_exists('precio_usd', $v)) {
                $precio = $v['precio_usd'];
                $datos['precio_usd'] = ($precio === null || $precio === '') ? null : $precio;
            }

            $existente = ! empty($v['id_variante'])
                ? $producto->variantes()->where('id_variante', $v['id_variante'])->first()
                : null;

            if ($existente) {
                $existente->update($datos);
                $conservadas[] = $existente->id_variante;
            } else {
                $conservadas[] = $producto->variantes()->create($datos)->id_variante;
            }
        }

        $sobrantes = $producto->variantes()->whereNotIn('id_variante', $conservadas)->get();

        foreach ($sobrantes as $variante) {
            try {
                // Transacción anidada = savepoint: un fallo de FK no aborta la transacción externa.
                DB::transaction(fn () => $variante->delete());
            } catch (QueryException $e) {
                $variante->update(['estado' => 'INACTIVO']);
            }
        }
    }

    /**
     * Elimina archivos del disco, tolerando rutas ya borradas.
     *
     * @param  string[]  $rutas
     */
    public function borrarArchivos(array $rutas): void
    {
        foreach ($rutas as $ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
