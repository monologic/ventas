<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PercepcionDetalle extends Model
{
    public $timestamps = false;

	protected $fillable = ['num_doc_rel', 'tipo_doc_rel', 'fecha_doc_rel', 'importe_doc_rel', 'moneda_doc_rel', 'fecha_cobro', 'numero_cobro', 'importe_cobro', 'moneda_cobro', 'importe_percibido', 'moneda_percibido', 'fecha_percepcion', 'monto_cobrar', 'moneda_cobrar', 'moneda_tipocambio', 'moneda_ob_tasacambio', 'factor_moneda', 'fecha_cambio', 'percepcion_id'];

	public function percepcion()
	{
	    return $this->belongsTo('App\Percepcion');
	}
}
