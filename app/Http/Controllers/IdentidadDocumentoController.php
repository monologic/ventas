<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\IdentidadDocumento;

class IdentidadDocumentoController extends Controller
{
    public function index()
    {
        $data = IdentidadDocumento::all();

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
        if(!$this->documentoVerification($request->numero)) {
            $data = new IdentidadDocumento($request->all());
            $data->save();

            $cCliente = new ClienteController();
            $ret = $cCliente->store($request, $data->id);        

            return $this->getDocumento($data->numero);
        }
        else
            return response()->json( false ); 
    }

    public function store2(Request $request)
    {
        $data = new IdentidadDocumento($request->all());
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
        $data = IdentidadDocumento::find($id);
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
        IdentidadDocumento::destroy($id);
    }

    public function getDocumento($numero)
    {
        $di = IdentidadDocumento::where('numero', $numero)->get();
        if (count($di) > 0) {
            $di = $di[0];
            $di->clientes;
            $di->clientes->each(function($data){
                $data->domicilio;
            });
        }

        return response()->json( $di );
    }

    public function documentoVerification($numero)
    {
        $di = IdentidadDocumento::where('numero', $numero)->get();
        if (count($di) > 0)
            $di = true;
        else
            $di = false;

        return $di;
    }

}
