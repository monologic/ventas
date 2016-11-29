<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    public $timestamps = false;

	protected $fillable = ['fecha_emision', 'numeracion', 'sum_otros_cargos', 'importe_total', 'tipo_moneda', 'version_ubl', 'version_doc', 'descuentos_glob', 'percepmonenac_importe_id', 'cliente_id', 'information_id', 'remision_guia_id', 'otros_documento_id', 'tipo_comprobante'];

	public function detalles()
	{
		return $this->hasMany('App\Detalle');
	}

	public function remisionGuia()
	{
	    return $this->belongsTo('App\RemisionGuia');
	}

	public function leyendas()
	{
	    return $this->belongsToMany('App\Leyenda');
	}

	public function otrosDocumentos()
	{
	    return $this->hasMany('App\OtrosDocumento');
	}

	public function information()
	{
	    return $this->belongsTo('App\Information');
	}

	public function dcNota()
	{
		return $this->hasOne('App\DcNotas');
	}

	public function cliente()
	{
	    return $this->belongsTo('App\Cliente');
	}

	public function percepcion()
	{
		return $this->hasOne('App\Percepcion');
	}

	public function retencion()
	{
		return $this->hasOne('App\Retencion');
	}

	public function percepmonenacImporte()
	{
	    return $this->belongsTo('App\PercepmonenacImporte');
	}

	public function montos()
	{
	    return $this->belongsToMany('App\Monto');
	}

	public function sumatorias()
	{
	    return $this->belongsToMany('App\Sumatoria');
	}
}
