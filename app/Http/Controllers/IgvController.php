<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class IgvController extends Controller
{
    public function store($request, $id)
    {
    	$data->monto_igv = $request['monto'];
    	$data->tributo_id = 1; //el id es 1 para IGV
    	$data->detalle_id = $id;

    	$data->save();
    }
}
