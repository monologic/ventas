<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leyenda extends Model
{
    public $timestamps = false;

	protected $fillable = ['codigo', 'descripcion'];

	public function comprobantes()
	{
	    return $this->belongsToMany('App\Comprobante');
	}
}
