<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tributo extends Model
{
    public $timestamps = false;

	protected $fillable = ['codigo', 'nombre', 'codigo_int'];

	public function igvs()
	{
		return $this->hasMany('App\Igv');
	}

	public function sistemaIscs()
	{
		return $this->hasMany('App\SistemaIsc');
	}

	public function sumatorias()
	{
		return $this->hasMany('App\Sumatoria');
	}
}
