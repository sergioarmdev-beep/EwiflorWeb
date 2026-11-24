<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\Catalogo\Models\Producto;
use Tests\Integration\PostgresTestCase;

class CatalogoPublicoTest extends PostgresTestCase
{
    /** @var array<int, int> */
    private array $idsProductos = [];

    protected function setUp(): void
    {
        parent::setUp();

        $prefijo = uniqid('pag', false);
        $idMarca = DB::table('marca')->insertGetId([
            'nombre' => 'Marca '.$prefijo,
        ], 'id_marca');

        for ($i = 1; $i <= 13; $i++) {
            $this->idsProductos[] = DB::table('producto')->insertGetId([
                'codigo' => 'PAG-'.$prefijo.'-'.$i,
                'nombre' => 'Producto Paginacion '.$prefijo.' '.$i,
                'descripcion' => 'Producto de prueba para paginación',
                'categoria' => 'REPOSTERIA',
                'id_marca' => $idMarca,
                'modelo' => 'M1',
                'tipo_producto' => 'MAQUINARIA',
                'modalidad' => 'INVENTARIABLE',
                'stock_minimo' => 0,
                'garantia_meses' => 12,
                'es_publico' => true,
                'estado' => 'ACTIVO',
            ], 'id_producto');
        }
    }

    public function test_catalogo_pagina_en_base_de_datos(): void
    {
        $total = Producto::publicos()->count();
        $this->assertGreaterThanOrEqual(13, $total);

        $primera = $this->get(route('publico.catalogo'));
        $primera->assertOk();
        $primera->assertViewIs('publico::catalogo');

        $paginator = $primera->viewData('paginator');
        $this->assertSame(12, $paginator->perPage());
        $this->assertSame($total, $paginator->total());
        $this->assertCount(12, $primera->viewData('productos'));
        $this->assertCount(12, $primera->viewData('detalle'));

        $segunda = $this->get(route('publico.catalogo', ['pagina' => 2]));
        $segunda->assertOk();
        $this->assertSame(2, $segunda->viewData('paginator')->currentPage());
        $this->assertCount($total - 12, $segunda->viewData('productos'));
    }

    public function test_filtro_por_categoria_reduce_el_total(): void
    {
        $esperado = Producto::publicos()->where('categoria', 'REPOSTERIA')->count();

        $response = $this->get(route('publico.catalogo', ['categoria' => 'REPOSTERIA']));

        $response->assertOk();
        $this->assertSame('REPOSTERIA', $response->viewData('categoriaActiva'));
        $this->assertSame($esperado, $response->viewData('paginator')->total());
        $this->assertGreaterThanOrEqual(13, $esperado);
    }

    public function test_busqueda_se_ejecuta_en_base_de_datos(): void
    {
        $response = $this->get(route('publico.catalogo', ['search' => 'Paginacion']));

        $response->assertOk();
        $this->assertGreaterThanOrEqual(13, $response->viewData('paginator')->total());
        $this->assertLessThanOrEqual(Producto::publicos()->count(), $response->viewData('paginator')->total());
    }
}
