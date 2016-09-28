<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    public $timestamps = false;

	protected $fillable = ['indicador', 'monto'];

	public function detalles()
	{
		return $this->hasMany('App\Detalle');
	}
}
