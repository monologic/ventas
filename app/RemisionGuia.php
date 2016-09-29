<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RemisionGuia extends Model
{
    public $timestamps = false;

	protected $fillable = ['numero', 'tipo_doc'];

	public function comprobantes()
	{
		return $this->hasMany('App\Comprobantes');
	}
}
