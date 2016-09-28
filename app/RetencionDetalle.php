<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetencionDetalle extends Model
{
    public $timestamps = false;

	protected $fillable = ['num_doc_rel', 'tipo_doc_rel', 'fecha_doc_rel', 'importe_doc_rel', 'moneda_doc_rel', 'fecha_pago', 'numero_pago', 'importe_pago', 'moneda_pago', 'importe_retenido', 'moneda_retenido', 'fecha_retencion', 'importe_neto', 'moneda_neto', 'moneda_tipocambio', 'moneda_ob_tasacambio', 'factor_moneda', 'fecha_cambio', 'retencion_id'];

	public function retencion()
	{
	    return $this->belongsTo('App\Retencion');
	}
}
