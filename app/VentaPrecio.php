<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VentaPrecio extends Model
{
    public $timestamps = false;

	protected $fillable = ['precio_venta', 'codigo'];

	public function detalles()
	{
	    return $this->hasMany('App\Detalle');
	}
}
