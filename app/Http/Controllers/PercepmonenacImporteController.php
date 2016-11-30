<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\PercepmonenacImporte;
use App\MontoTipo;

class PercepmonenacImporteController extends Controller
{
    public function store($percepcion, $id)
	{
    	if ($percepcion['monto'] != 0) {
			$t = MontoTipo::where('codigo', $percepcion['codigo'])->get();
			$s = new PercepmonenacImporte();
			$s->base_imponible = $percepcion['base_imponible'];
			$s->monto = $percepcion['monto'];
			$s->monto_total = $percepcion['monto_total'];
			$s->monto_tipo_id = $t[0]->id;
			$s->comprobante_id = $id;
			$s->save();
			$sumatoriasId[] = $s->id;
		}
	}
}
