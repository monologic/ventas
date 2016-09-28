<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sumatoria extends Model
{
    public $timestamps = false;

	protected $fillable = ['sumatoria', 'tributo_id'];

	public function tributo()
	{
	    return $this->belongsTo('App\Tributo');
	}

	public function comprobantes()
    {
        return $this->belongsToMany('App\Comprobante');
    }
}
