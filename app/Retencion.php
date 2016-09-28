<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Retencion extends Model
{
    public $timestamps = false;

	protected $fillable = ['regimen', 'tasa', 'observaciones', 'importe_retenido', 'moneda_retenido', 'importe_pagado', 'moneda_pagado', 'comprobante_id'];

	public function retencionDetalles()
	{
		return $this->hasMany('App\RetencionDetalle');
	}

	public function comprobante()
	{
	    return $this->belongsTo('App\Comprobante');
	}
}
