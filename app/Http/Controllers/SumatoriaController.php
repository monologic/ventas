<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Sumatoria;
use App\Tributo;

class SumatoriaController extends Controller
{
	public function store($sumatorias)
	{
		$sumatoriasId = array();
    	foreach ($sumatorias as $sumatoria) {
    		if ($sumatoria['monto'] != 0) {
    			$t = Tributo::where('codigo', $sumatoria['codigo_tributo'])->get();
	    		$s = new Sumatoria();
	    		$s->sumatoria = $sumatoria['monto'];
	    		$s->tributo_id = $t[0]->id;
	    		$s->save();
	    		$sumatoriasId[] = $s->id;
    		}
    		
    	}

    	return $sumatoriasId;
	}
}
