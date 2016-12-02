<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Monto;
use App\MontoTipo;

class MontoController extends Controller
{
    public function store($montos)
	{
		$montosId = array();
    	foreach ($montos as $monto) {
    		//print_r($monto);
    		if ($monto['monto'] != 0) {
    			$t = MontoTipo::where('codigo', $monto['codigo'])->get();
	    		$s = new Monto();
	    		$s->monto = $monto['monto'];
	    		$s->monto_tipo_id = $t[0]->id;
	    		if (array_key_exists('porcentaje', $monto)) {
	    			$s->porcentaje = $monto['porcentaje'];
	    		}
	    		if (array_key_exists('numero_cuenta', $monto)) {
	    			$s->numero_cuenta = $monto['numero_cuenta'];
	    		}
	    		$s->save();
	    		$montosId[] = $s->id;
    		}
    		
    	}

    	return $montosId;
	}
}
