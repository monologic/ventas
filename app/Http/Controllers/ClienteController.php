<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Cliente;
use App\IdentidadDocumento;

class ClienteController extends Controller
{
    public function index()
    {
        $data = Cliente::all();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $idDoc)
    {
        $data = new Cliente();
        $data->nombre = $request->nombre;
        $data->agente_percep = $request->agente_percep;
        $data->agente_retencion = $request->agente_retencion;
        $data->identidad_documento_id = $idDoc;
        $data->save();

        return $data->id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Cliente::find($id);
        $data->fill($request->all());
        $data->save();

        return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Cliente::destroy($id);
    }

}