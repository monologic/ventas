<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdentidadDocumento extends Model
{
    public $timestamps = false;

	protected $fillable = ['numero', 'tipo_doc'];

	public function clientes()
	{
		return $this->hasMany('App\Cliente');
	}

	public function informations()
	{
		return $this->hasMany('App\Information');
	}

}
