<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Igv extends Model
{
    public $timestamps = false;

	protected $fillable = ['monto_igv', 'afectacion_igv', 'tributo_id'];

	public function tributo()
    {
        return $this->belongsTo('App\Tributo');
    }

	public function detalles()
	{
		return $this->hasMany('App\Detalle');
	}
}
