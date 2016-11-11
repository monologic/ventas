<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Departamento;

class DepartamentoController extends Controller
{
    public function index()
    {
        $data = Departamento::all();
        $data->each(function($data){
        	$data->provincias;
        	$data->provincias->each(function($data){
        		$data->distritos;
        	});
		});

        return response()->json($data);
    }
}
