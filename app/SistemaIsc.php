<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SistemaIsc extends Model
{
    public $timestamps = false;

	protected $fillable = ['monto_isc', 'tipo_sistema', 'tributo_id'];

	public function tributo()
    {
        return $this->belongsTo('App\Tributo');
    }

	public function detalles()
	{
		return $this->hasMany('App\Detalle');
	}
}
