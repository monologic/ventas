<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModDoc extends Model
{
    public $timestamps = false;

	protected $fillable = ['dc_nota_id', 'numero'];

	public function dcNota()
	{
	    return $this->belongsTo('App\DcNota');
	}
}
