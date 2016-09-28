<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
	public $timestamps = false;

	protected $fillable = ['descripcion', 'valor_unitario', 'codigo'];

	public function detalles()
	{
	    return $this->hasMany('App\Detalle');
	}

}
