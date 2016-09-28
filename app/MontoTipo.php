<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MontoTipo extends Model
{
    public $timestamps = false;

	protected $fillable = ['descripcion', 'codigo'];

	public function percepmonenacImportes()
	{
		return $this->hasMany('App\PercepmonenacImporte');
	}

	public function montos()
	{
		return $this->hasMany('App\Monto');
	}
}
