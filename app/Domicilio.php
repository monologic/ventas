<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Domicilio extends Model
{
    public $timestamps = false;

	protected $fillable = ['cod_ubigeo', 'direccion_completa', 'urbanizacion', 'provincia', 'departamento', 'distrito', 'cod_pais'];

	public function cliente()
	{
		return $this->hasOne('App\Cliente');
	}
	public function information()
	{
		return $this->hasOne('App\Information');
	}

}
