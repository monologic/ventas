<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class DetalleController extends Controller
{
    public function store($request, $id)
    {
    	foreach ($request as $detalle) {
    		$data->cantidad = $detalle['cantidad'];
    		$data->valor_venta = $detalle['valor_venta'];
    		$data->orden = $detalle['orden'];
    		$data->comprobante_id = $id;
    		$data->producto_id = $detalle['idProducto'];
    		$data->precio_venta = $detalle['precio_venta']['monto'];

    		$data->save();

    		if ($comprobante['detalles']['afectacion_igv']['monto'] != 0) {
    			$controllerIGV = new IgvController();
        		$controllerIGV->store($comprobante['detalles']['afectacion_igv'], $data->id);
        	}

        	if ($comprobante['detalles']['afectacion_isc']['monto'] != 0) {
        		$controllerISC = new SistemaIscController();
        		$controllerISC->store($comprobante['detalles']['afectacion_isc'], $data->id);
        	}
    	}
    }
}
