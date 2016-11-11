<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    public function provincias()
	{
		return $this->hasMany('App\Provincia');
	}
}
