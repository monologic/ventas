<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Percepcion extends Model
{
    public $timestamps = false;

	protected $fillable = ['regimen', 'tasa', 'observaciones', 'importe_percibido', 'moneda_percibido', 'importe_cobrado', 'moneda_cobrado', 'comprobante_id'];

	public function percepcionDetalles()
	{
		return $this->hasMany('App\PercepcionDetalle');
	}

	public function comprobante()
	{
	    return $this->belongsTo('App\Comprobante');
	}
}
