<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtrosDocumento extends Model
{
    public $timestamps = false;

	protected $fillable = ['numero', 'codigo', 'comprobante_id'];

	public function comprobante()
	{
	    return $this->belongsTo('App\Comprobante');
	}
}
