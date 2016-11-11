<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    public function departamento()
	{
	    return $this->belongsTo('App\Departamento');
	}

	public function distritos()
	{
		return $this->hasMany('App\Distrito');
	}
}
