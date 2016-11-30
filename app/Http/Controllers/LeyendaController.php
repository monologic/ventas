<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Leyenda;

class LeyendaController extends Controller
{
    public function getIdLeyendas($leyendas)
    {
    	$leyendasId = array();
    	foreach ($leyendas as $leyenda) {
    		$l = Leyenda::where('codigo', $leyenda['codigo'])->get();
    		$l = $l[0];

    		$leyendasId[] = $l->id;
    	}

    	return $leyendasId;
    }
}
