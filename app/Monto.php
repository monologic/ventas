<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monto extends Model
{
    public $timestamps = false;

	protected $fillable = ['monto', 'monto_tipo_id'];

	public function comprobantes()
	{
	    return $this->belongsToMany('App\Comprobante');
	}

	public function montoTipo()
	{
	    return $this->belongsTo('App\MontoTipo');
	}
}
