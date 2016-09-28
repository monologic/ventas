<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComprobanteTipo extends Model
{
    public $timestamps = false;

	protected $fillable = ['descripcion', 'codigo'];

	public function comprobantes()
	{
		return $this->hasMany('App\Comprobante');
	}

	public function dcNotas()
	{
		return $this->hasMany('App\DcNota');
	}
}
