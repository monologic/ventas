<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Detalle extends Model
{
    public $timestamps = false;

	protected $fillable = ['unidad_medida', 'cantidad', 'valor_venta', 'orden', 'comprobante_id', 'igv_id', 'sistema_isc_id', 'descuento_id', 'producto_id', 'venta_precio_id', 'valores_referencial_id'];

	public function comprobante()
    {
        return $this->belongsTo('App\Comprobante');
    }

    public function igv()
    {
        return $this->belongsTo('App\Igv');
    }

    public function sistemaIsc()
    {
        return $this->belongsTo('App\SistemaIsc');
    }

    public function descuento()
    {
        return $this->belongsTo('App\Descuento');
    }

    public function producto()
    {
        return $this->belongsTo('App\Producto');
    }

    public function ventaPrecio()
    {
        return $this->belongsTo('App\VentaPrecio');
    }

    public function valoresReferencial()
    {
        return $this->belongsTo('App\ValoresReferencial');
    }
}
