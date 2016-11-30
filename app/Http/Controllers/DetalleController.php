<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Detalle;

class DetalleController extends Controller
{
    public function store($request, $id)
    {
    	foreach ($request as $detalle) {
    		$data = new Detalle();
    		$data->cantidad = $detalle['cantidad'];
    		$data->valor_venta = $detalle['valor_venta'];
    		$data->orden = $detalle['numero_item'];
    		$data->comprobante_id = $id;
    		$data->producto_id = $detalle['idProducto'];
    		$data->precio_venta = $detalle['precio_venta']['monto'];

    		$data->save();

    		if ($detalle['afectacion_igv']['monto'] != 0) {
    			$controllerIGV = new IgvController();
        		$controllerIGV->store($detalle['afectacion_igv'], $data->id);
        	}

        	if ($detalle['afectacion_isc']['monto'] != 0) {
        		$controllerISC = new SistemaIscController();
        		$controllerISC->store($detalle['afectacion_isc'], $data->id);
        	}
    	}
    }
}
