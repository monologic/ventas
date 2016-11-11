<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Information;

class InformationController extends Controller
{
    public function index()
    {
        $data = Information::all();
        if (count($data) > 0) {
            $data = $data[0];
            $data->domicilio;
            $data->identidadDocumento;
        }
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cID = new IdentidadDocumentoController();
        $identidad_documento_id = $cID->store2($request); //Recibe el id del documento de identidad guardado

        $cDom = new DomicilioController();
        $domicilio_id = $cDom->store($request); //Recibe el id del documento de identidad guardado

        $data = new Information($request->all());
        $data->identidad_documento_id = $identidad_documento_id;
        $data->domicilio_id = $domicilio_id;

        $data->save();
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
        $data = Information::find($id);
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
        Information::destroy($id);
    }
}
