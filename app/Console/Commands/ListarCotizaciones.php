<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListarCotizaciones extends Command
{
    protected $signature = 'ewiflor:cotizaciones:listar';

    protected $description = 'Lista las cotizaciones con el número de líneas que tiene cada una';

    public function handle(): int
    {
        $cotizaciones = DB::table('cotizacion as c')
            ->leftJoin('detalle_cotizacion as d', 'd.id_cotizacion', '=', 'c.id_cotizacion')
            ->groupBy('c.id_cotizacion', 'c.estado')
            ->orderBy('c.id_cotizacion')
            ->get([
                'c.id_cotizacion',
                'c.estado',
                DB::raw('count(d.id_cotizacion) as lineas'),
            ]);

        $this->table(
            ['ID', 'Estado', 'Líneas'],
            $cotizaciones->map(fn ($c) => [
                $c->id_cotizacion,
                $c->estado,
                $c->lineas,
            ])->all()
        );

        return self::SUCCESS;
    }
}
