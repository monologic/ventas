<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
	public $timestamps = false;

	protected $fillable = ['descripcion', 'valor_unitario', 'codigo', 'afectacion_igv', 'tasa_isc', 'cod_tipo_sistema_isc','tasa_percep', 'tasa_detracc'];

	public function detalles()
	{
	    return $this->hasMany('App\Detalle');
	}

}
