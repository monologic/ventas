<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DcNota extends Model
{
    public $timestamps = false;

	protected $fillable = ['doc_afecatado', 'codigo_tipo', 'motivo', 'comprobante_id', 'comprobante_tipo_id'];

	public function modDocs()
	{
		return $this->hasMany('App\ModDoc');
	}

	public function comprobante()
	{
	    return $this->belongsTo('App\Comprobante');
	}

	public function comprobanteTipo()
	{
	    return $this->belongsTo('App\ComprobanteTipo');
	}
}
