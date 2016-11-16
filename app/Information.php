<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
	protected $table = 'informations';

    public $timestamps = false;

	protected $fillable = ['firma_digital', 'nombre', 'nombre_comercial', 'domicilio_id', 'identidad_documento_id', 'igv', 'agente_percep', 'agente_retencion', 'comprobante_percepcion'];

	public function domicilio()
	{
	    return $this->belongsTo('App\Domicilio');
	}

	public function identidadDocumento()
	{
	    return $this->belongsTo('App\IdentidadDocumento');
	}
}
