<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ValoresReferencial extends Model
{
    public $timestamps = false;

	protected $fillable = ['monto', 'codigo'];

	public function detalles()
	{
	    return $this->hasMany('App\Detalle');
	}
}
