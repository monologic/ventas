<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    public $timestamps = false;

	protected $fillable = ['nombre', 'identidad_documento_id', 'domicilio_id'];

	public function comprobantes()
	{
		return $this->hasMany('App\Comprobante');
	}

	public function identidadDocumento()
	{
	    return $this->belongsTo('App\IdentidadDocumento');
	}

	public function domicilio()
	{
	    return $this->belongsTo('App\Domicilio');
	}
}
