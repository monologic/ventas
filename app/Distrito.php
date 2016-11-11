<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    public function provincia()
	{
	    return $this->belongsTo('App\Provincia');
	}
}
