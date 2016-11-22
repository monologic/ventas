<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Comprobante;

class ComprobanteController extends Controller
{
    public function index()
    {
        $data = Comprobante::all();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$comprobante = json_decode($request->json, true);
		//dd($comprobante);
        $this->direccionarTipoComprobante($comprobante);
        /*
        $data = new Comprobante();
        
        $data->save();

        return $data->id;*/
    }

    public function direccionarTipoComprobante( $comprobante )
    {
        if ($comprobante['tipoDocumento'] == "01")
            $this->createXMLDomFactura($comprobante);

    }

    public function createXMLDomFactura($comprobante)
    {
        // "Create" the document.
        $xml = new \DOMDocument( "1.0", "ISO-8859-1" );
        $xml->standalone = false;
        $invoice = $xml->createElement( "Invoice" );
        $invoice->setAttribute( "xmlns", "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" );
        $invoice->setAttribute( "xmlns:cac", "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" );
        $invoice->setAttribute( "xmlns:cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" );
        $invoice->setAttribute( "xmlns:ccts", "urn:un:unece:uncefact:documentation:2" );
        $invoice->setAttribute( "xmlns:ds", "http://www.w3.org/2000/09/xmldsig#" );
        $invoice->setAttribute( "xmlns:ext", "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" );
        $invoice->setAttribute( "xmlns:qdt", "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" );
        $invoice->setAttribute( "xmlns:sac", "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" );
        $invoice->setAttribute( "xmlns:udt", "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" );
        $invoice->setAttribute( "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance" );
        
        $xml->appendChild( $invoice );
        
        $ublExtensions = $xml->createElement( "ext:UBLExtensions" );
        $invoice->appendChild( $ublExtensions );
        $ublExtension1 = $xml->createElement( "ext:UBLExtension" );
        $ublExtensions->appendChild( $ublExtension1 );
        $ExtContent = $xml->createElement( "ext:ExtensionContent" );
        $ublExtension1->appendChild( $ExtContent );
        $AdInfo = $xml->createElement( "sac:AdditionalInformation" );
        $ExtContent->appendChild( $AdInfo );
        
        foreach ($comprobante['totalValorVenta'] as $i => $value) {
            if ($value['monto'] != 0) {
                $AdMoneTotal = $xml->createElement( "sac:AdditionalMonetaryTotal" );
                $AdInfo->appendChild( $AdMoneTotal );
                $id = $xml->createElement( "cbc:ID", $value['codigo'] );
                $AdMoneTotal->appendChild( $id );
                $payAmount = $xml->createElement( "cbc:PayableAmount", $value['monto'] );
                $payAmount->setAttribute("currencyID", "PEN");
                $AdMoneTotal->appendChild( $payAmount );
            }
        }
        if (array_key_exists('percepcion', $comprobante)) {
            $AdMoneTotal = $xml->createElement( "sac:AdditionalMonetaryTotal" );
            $AdInfo->appendChild( $AdMoneTotal );
            $id = $xml->createElement( "cbc:ID", $comprobante['percepcion']['codigo'] );
            $AdMoneTotal->appendChild( $id );
            $monto = $comprobante['percepcion']['monto'];
            $payAmount = $xml->createElement( "cbc:PayableAmount", round($monto, 2) );
            $payAmount->setAttribute("currencyID", "PEN");
            $AdMoneTotal->appendChild( $payAmount );
            $total = $comprobante['percepcion']['monto_total'];
            $payAmount = $xml->createElement( "sac:TotalAmount", round($total, 2) );
            $payAmount->setAttribute("currencyID", "PEN");
            $AdMoneTotal->appendChild( $payAmount );
        }

        if (array_key_exists('detraccionTotal', $comprobante)) {
            $AdMoneTotal = $xml->createElement( "sac:AdditionalMonetaryTotal" );
            $AdInfo->appendChild( $AdMoneTotal );
            
            $id = $xml->createElement( "cbc:ID", $comprobante['detraccionTotal']['codigo'] );
            $AdMoneTotal->appendChild( $id );
            
            $monto = $comprobante['detraccionTotal']['monto'];
            $payAmount = $xml->createElement( "cbc:PayableAmount", round($monto, 2) );
            $payAmount->setAttribute("currencyID", "PEN");
            $AdMoneTotal->appendChild( $payAmount );

            if (array_key_exists('pocentaje', $comprobante['detraccionTotal'])) {
                $porcentaje = $comprobante['detraccionTotal']['porcentaje'];
                $percent = $xml->createElement( "sac:Percent", $porcentaje);
                $AdMoneTotal->appendChild( $percent );
            }

            $AdProp = $xml->createElement( "sac:AdditionalProperty" );
            $AdInfo->appendChild( $AdProp );
            $id = $xml->createElement( "cbc:ID", "3001" );
            $AdProp->appendChild( $id );
            $value = $xml->createElement( "cbc:Value", $comprobante['detraccionTotal']['numero_cuenta'] );
            $AdProp->appendChild( $value );

        }

        foreach ($comprobante['leyendas'] as $i => $value) {
                $AdProp = $xml->createElement( "sac:AdditionalProperty" );
                $AdInfo->appendChild( $AdProp );
                $id = $xml->createElement( "cbc:ID", $value['codigo'] );
                $AdProp->appendChild( $id );
                $value = $xml->createElement( "cbc:Value", $value['value'] );
                $AdProp->appendChild( $value );
        }




        //$id = $xml->createElement( "cbc:ID", "The last symphony composed by Ludwig van Beethoven." );
        
/*

<sac:AdditionalMonetaryTotal>
    <cbc:ID>2003</cbc:ID>
    <cbc:PayableAmount currencyID="PEN">2208.96</cbc:PayableAmount>
    <sac:Percent>9.00%</sac:Percent>
</sac:AdditionalMonetaryTotal>


<sac:AdditionalMonetaryTotal>
            <cbc:ID>2001</cbc:ID>
            <cbc:PayableAmount currencyID="PEN">1427.10</cbc:PayableAmount>
            <sac:TotalAmount currencyID="PEN">72782.09</sac:TotalAmount>
 </sac:AdditionalMonetaryTotal>

            <sac:AdditionalInformation>
                <sac:AdditionalMonetaryTotal>
                    <cbc:ID>1001</cbc:ID>
                    <cbc:PayableAmount currencyID="PEN">348199.15</cbc:PayableAmount>
                </sac:AdditionalMonetaryTotal>
                <sac:AdditionalMonetaryTotal>
                    <cbc:ID>1003</cbc:ID>
                    <cbc:PayableAmount currencyID="PEN">12350.00</cbc:PayableAmount>
                </sac:AdditionalMonetaryTotal>
                <sac:AdditionalMonetaryTotal>
                    <cbc:ID>1004</cbc:ID>
                    <cbc:PayableAmount currencyID="PEN">30.00</cbc:PayableAmount>
                </sac:AdditionalMonetaryTotal>
                <sac:AdditionalMonetaryTotal>
                    <cbc:ID>2005</cbc:ID>
                    <cbc:PayableAmount currencyID="PEN">59230.51</cbc:PayableAmount>
                </sac:AdditionalMonetaryTotal>
                <sac:AdditionalProperty>
                    <cbc:ID>1000</cbc:ID>
                    <cbc:Value>CUATROCIENTOS VEINTITRES MIL DOSCIENTOS VEINTICINCO Y 00/100</cbc:Value>
                </sac:AdditionalProperty>
            </sac:AdditionalInformation>



        */
        //dd($comprobante);
        dd($xml);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Comprobante::find($id);
        $data->fill($request->all());
        $data->save();

        return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Comprobante::destroy($id);
    }
}
