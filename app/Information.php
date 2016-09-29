<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    public $timestamps = false;

	protected $fillable = ['firma_digital', 'nombre', 'combre_comercial', 'domicilio_id', 'identidad_documento_id'];

	public function domicilio()
	{
	    return $this->belongsTo('App\Domicilio');
	}

	public function identidadDocumento()
	{
	    return $this->belongsTo('App\IdentidadDocumento');
	}
}
