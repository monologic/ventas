<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PercepmonenacImporte extends Model
{
    public $timestamps = false;

	protected $fillable = ['base_imponible', 'monto', 'monto_total', 'monto_tipo_id'];

	public function comprobante()
	{
		return $this->hasOne('App\Comprobante');
	}

	public function montoTipo()
	{
	    return $this->belongsTo('App\MontoTipo');
	}
}
