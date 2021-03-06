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
        
        
        $data->save();

        return $data->id;*/
    }

    public function storeFactura( $comprobante )
    {
        $data = new Comprobante();
        $data->fecha_emision = date("Y-m-d");
        $data->numeracion = $this->crearNumeracion($comprobante['tipoDocumento']);
        $data->importe_total = $comprobante['importeTotal'];
        $data->tipo_moneda = $comprobante['moneda'];
        $data->version_ubl = $comprobante['version_UBL'];
        $data->version_doc = $comprobante['version_doc'];
        $data->cliente_id = $comprobante['cliente']['clientes'][0]['id'];
        $data->information_id = $comprobante['information']['id'];
        $data->user_id = \Auth::user()->id;
        $data->tipo_comprobante = $comprobante['tipoDocumento'];

        $data->save();

        $controllerDetalle = new DetalleController();
        $controllerDetalle->store($comprobante['detalles'], $data->id);

        $leyendaController = new LeyendaController();
        $arrayData = $leyendaController->getIdLeyendas($comprobante['leyendas']);
        $data->leyendas()->sync($arrayData);

        $sumatoriaController = new SumatoriaController();
        $arrayData = $sumatoriaController->store($comprobante['sumatoriasImpuestos']);
        $data->sumatorias()->sync($arrayData);

        $montoController = new MontoController();
        $arrayData = $montoController->store($comprobante['totalValorVenta']);
        $data->montos()->sync($arrayData);

        if (array_key_exists('detraccion', $comprobante)) {
            $montoController = new MontoController();
            $arrayData = $montoController->store($comprobante['detraccion']);
            $data->montos()->sync($arrayData);
        }

        if (array_key_exists('retencion', $comprobante)) {
            $retenciones[] = $comprobante['retencion'];
            $montoController = new MontoController();
            $arrayData = $montoController->store($retenciones);
            $data->montos()->sync($arrayData);
        }

        if (array_key_exists('percepcion', $comprobante)) {
            $percepController = new PercepmonenacImporteController();
            $percepController->store($comprobante['percepcion'], $data->id);
        }
        
        return $data->numeracion;

    }


    public function direccionarTipoComprobante( $comprobante )
    {
        if ($comprobante['tipoDocumento'] == "01"){
            $numeracion = $this->storeFactura($comprobante);
            $this->createXMLDomFactura($comprobante, $numeracion);
        }
    }

    public function createXMLDomFactura( $comprobante, $numeracion )
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

        /* Total y Leyendas */
        
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
        /* Firma Digital */
        $ublExtension2 = $xml->createElement( "ext:UBLExtension" );
        $ublExtensions->appendChild( $ublExtension2 );
        
            $ExtContent = $xml->createElement( "ext:ExtensionContent" );
            $ublExtension2->appendChild( $ExtContent );
        
                $signature = $xml->createElement( "ds:Signature" );
                $signature->setAttribute("Id", "SignatureCF");
                $ExtContent->appendChild( $signature );
                
                    $signedInfo = $xml->createElement( "ds:SignedInfo" );
                    $signature->appendChild( $signedInfo );
                    
                        $canonMethod = $xml->createElement( "ds:CanonicalizationMethod" );
                        $canonMethod->setAttribute("Algorithm", "http://www.w3.org/TR/2001/REC-xml-c14n-20010315");
                        $signedInfo->appendChild( $canonMethod );
                        
                        $signMethod = $xml->createElement( "ds:SignatureMethod" );
                        $signMethod->setAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#rsa-sha1");
                        $signedInfo->appendChild( $signMethod );

                        $reference = $xml->createElement( "ds:Reference" );
                        $reference->setAttribute("URI", "");
                        $signedInfo->appendChild( $reference );

                            $transforms = $xml->createElement( "ds:Transforms" );
                            $reference->appendChild( $transforms );

                                $transform = $xml->createElement( "ds:Transform" );
                                $transform->setAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#envelopedsignature");
                                $transforms->appendChild( $transform );

                            $digestMethod = $xml->createElement( "ds:DigestMethod" );
                            $digestMethod->setAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#sha1");
                            $reference->appendChild( $digestMethod );

                            $digestValue = $xml->createElement( "ds:DigestValue", "" );
                            $reference->appendChild( $digestValue );

                    $SignatureValue = $xml->createElement( "ds:SignatureValue", "" );
                    $signature->appendChild( $SignatureValue );

                    $KeyInfo = $xml->createElement( "ds:KeyInfo");
                    $signature->appendChild( $KeyInfo );

                        $X509Data = $xml->createElement( "ds:X509Data");
                        $KeyInfo->appendChild( $X509Data );

                            $X509Certificate = $xml->createElement( "ds:X509Certificate", "MIIESTCCAzGgAwIBAgIKWOCRzgAAAAAAIjANBgkqhkiG9w0BAQUFADAnMRUwEwYKCZImiZPyLGQBGRYFU1VOQVQxDjAMBgNVBAMTBVNVTkFUMB4XDTEwMTIyODE5NTExMFoXDTExMTIyODIwMDExMFowgZUxCzAJBgNVBAYTAlBFMQ0wCwYDVQQIEwRMSU1BMQ0wCwYDVQQHEwRMSU1BMREwDwYDVQQKEwhTT1VUSEVSTjEUMBIGA1UECxMLMjAxMDAxNDc1MTQxFDASBgNVBAMTC0JvcmlzIFN1bGNhMSkwJwYJKoZIhvcNAQkBFhpCU1VMQ0FAU09VVEhFUk5QRVJVLkNPTS5QRTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAtRtcpfBLzyajuEmYt4mVH8EE02KQiETsdKStUThVYM7g3Lkx5zq3SH5nLH00EKGCtota6RR+V40sgIbnh+Nfs1SOQcAohNwRfWhho7sKNZFR971rFxj4cTKMEvpt8Dr98UYFkJhph6WnsniGM2tJDq9KJ52UXrlScMfBityx0AsCAwEAAaOCAYowggGGMA4GA1UdDwEB/wQEAwIE8DBEBgkqhkiG9w0BCQ8ENzA1MA4GCCqGSIb3DQMCAgIAgDAOBggqhkiG9w0DBAICAIAwBwYFKw4DAgcwCgYIKoZIhvcNAwcwHQYDVR0OBBYEFG/m6twbiRNzRINavjq+U0j/sZECMBMGA1UdJQQMMAoGCCsGAQUFBwMCMB8GA1UdIwQYMBaAFN9kHQDqWONmozw3xdNSIMFW2t+7MFkGA1UdHwRSMFAwTqBMoEqGImh0dHA6Ly9wY2IyMjYvQ2VydEVucm9sbC9TVU5BVC5jcmyGJGZpbGU6Ly9cXHBjYjIyNlxDZXJ0RW5yb2xsXFNVTkFULmNybDB+BggrBgEFBQcBAQRyMHAwNQYIKwYBBQUHMAKGKWh0dHA6Ly9wY2IyMjYvQ2VydEVucm9sbC9wY2IyMjZfU1VOQVQuY3J0MDcGCCsGAQUFBzAChitmaWxlOi8vXFxwY2IyMjZcQ2VydEVucm9sbFxwY2IyMjZfU1VOQVQuY3J0MA0GCSqGSIb3DQEBBQUAA4IBAQBI6wJ/QmRpz3C3rorBflOvA9DOa3GNiiB7rtPIjF4mPmtgfo2pK9gvnxmV2pST3ovfu0nbG2kpjzzaaelRjEodHvkcM3abGsOE53wfxqQF5uf/jkzZA9hbLHtE1aLKBD0Mhzc6cvI072alnE6QU3RZ16ie9CYsHmMrs+sPHMy8DJU5YrdnqHdSn2D3nhKBi4QfT/WURPOuo6DF4iWgrCyMf3eJgmGKSUN3At5fK4HSpfyURT0kboaJKNBgQwy0HhGh5BLM7DsTi/KwfdUYkoFgrY71Pm23+ra+xTow1Vk9gj5NqrlpMY5gAVQXEIo1++GxDtaK/5EiVKSqzJ6geIfz");
                            $X509Data->appendChild( $X509Certificate );

        $Signature = $xml->createElement( "cac:Signature" );
        $invoice->appendChild( $Signature );

            $ID = $xml->createElement( "cbc:ID", "IDSignSP" );
            $Signature->appendChild( $ID );

            $SignatoryParty = $xml->createElement( "cac:SignatoryParty" );
            $Signature->appendChild( $SignatoryParty );

                $PartyIdentification = $xml->createElement( "cac:PartyIdentification" );
                $SignatoryParty->appendChild( $PartyIdentification );

                    $ID = $xml->createElement( "cbc:ID", $comprobante['information']['identidad_documento']['numero'] );
                    $PartyIdentification->appendChild( $ID );

                $PartyName = $xml->createElement( "cac:PartyName" );
                $SignatoryParty->appendChild( $PartyName );

                    $Name = $xml->createElement( "cbc:Name", $comprobante['information']['nombre'] );
                    $PartyName->appendChild( $Name );

            $DigitalSignatureAttachment = $xml->createElement( "cac:DigitalSignatureAttachment" );
            $Signature->appendChild( $DigitalSignatureAttachment );

                $ExternalReference = $xml->createElement( "cac:ExternalReference" );
                $DigitalSignatureAttachment->appendChild( $ExternalReference );

                    $URI = $xml->createElement( "cbc:URI", "#SignatureCF" );
                    $ExternalReference->appendChild( $URI );

        /* Info empresa */ 
        $AccountingSupplierParty = $xml->createElement( "cac:AccountingSupplierParty" );
        $invoice->appendChild( $AccountingSupplierParty );

            $CustomerAssignedAccountID = $xml->createElement( "cbc:CustomerAssignedAccountID", $comprobante['information']['identidad_documento']['numero'] );
            $AccountingSupplierParty->appendChild( $CustomerAssignedAccountID );

            $Party = $xml->createElement( "cac:Party" );
            $AccountingSupplierParty->appendChild( $Party );

                $PartyName = $xml->createElement( "cac:PartyName" );
                $Party->appendChild( $PartyName );

                    $Name = $xml->createElement( "cbc:Name", $comprobante['information']['nombre_comercial'] );
                    $PartyName->appendChild( $Name );

                if (count($comprobante['information']['domicilio']) != 0) {
                    $PostalAddress = $xml->createElement( "cac:PostalAddress" );
                    $Party->appendChild( $PostalAddress );

                        $ID = $xml->createElement( "cbc:ID", $comprobante['information']['domicilio']['cod_ubigeo'] );
                        $PostalAddress->appendChild( $ID );

                        $StreetName = $xml->createElement( "cbc:StreetName", $comprobante['information']['domicilio']['direccion_completa'] );
                        $PostalAddress->appendChild( $StreetName );

                        $CitySubdivisionName = $xml->createElement( "cbc:CitySubdivisionName", $comprobante['information']['domicilio']['urbanizacion'] );
                        $PostalAddress->appendChild( $CitySubdivisionName );

                        $CityName = $xml->createElement( "cbc:CityName", $comprobante['information']['domicilio']['departamento'] );
                        $PostalAddress->appendChild( $CityName );

                        $CountrySubentity = $xml->createElement( "cbc:CountrySubentity", $comprobante['information']['domicilio']['provincia'] );
                        $PostalAddress->appendChild( $CountrySubentity );

                        $District = $xml->createElement( "cbc:District", $comprobante['information']['domicilio']['distrito'] );
                        $PostalAddress->appendChild( $District );

                        $Country = $xml->createElement( "cac:Country" );
                        $PostalAddress->appendChild( $Country );

                            $IdentificationCode = $xml->createElement( "cbc:IdentificationCode", $comprobante['information']['domicilio']['cod_pais'] );
                            $Country->appendChild( $IdentificationCode );
                }

                $PartyLegalEntity = $xml->createElement( "cac:PartyLegalEntity" );
                $Party->appendChild( $PartyLegalEntity );

                    $RegistrationName = $xml->createElement( "cbc:RegistrationName", $comprobante['information']['nombre'] );
                    $PartyLegalEntity->appendChild( $RegistrationName );

        /* Informacion general */
        $UBLVersionID = $xml->createElement( "cbc:UBLVersionID", $comprobante['version_UBL'] );
        $invoice->appendChild( $UBLVersionID );

        $CustomizationID = $xml->createElement( "cbc:CustomizationID", $comprobante['version_doc'] );
        $invoice->appendChild( $CustomizationID );

        // Falta el numero de factura 
        $ID = $xml->createElement( "cbc:ID", $numeracion );
        $invoice->appendChild( $ID );     

        $IssueDate = $xml->createElement( "cbc:IssueDate", date("Y-m-d") );
        $invoice->appendChild( $IssueDate );

        $InvoiceTypeCode = $xml->createElement( "cbc:InvoiceTypeCode", $comprobante['tipoDocumento'] );
        $invoice->appendChild( $InvoiceTypeCode );

        $DocumentCurrencyCode = $xml->createElement( "cbc:DocumentCurrencyCode", $comprobante['moneda'] );
        $invoice->appendChild( $DocumentCurrencyCode );

        /* Información del CLiente */
        $AccountingCustomerParty = $xml->createElement( "cac:AccountingCustomerParty" );
        $invoice->appendChild( $AccountingCustomerParty );

            $CustomerAssignedAccountID = $xml->createElement( "cbc:CustomerAssignedAccountID", $comprobante['cliente']['numero'] );
            $AccountingCustomerParty->appendChild( $CustomerAssignedAccountID );

            $AdditionalAccountID = $xml->createElement( "cbc:AdditionalAccountID", $comprobante['cliente']['tipo_doc'] );
            $AccountingCustomerParty->appendChild( $AdditionalAccountID );

            $Party = $xml->createElement( "cac:Party" );
            $AccountingCustomerParty->appendChild( $Party );

                $PartyLegalEntity = $xml->createElement( "cac:PartyLegalEntity" );
                $Party->appendChild( $PartyLegalEntity );

                    $RegistrationName = $xml->createElement( "cbc:RegistrationName", $comprobante['cliente']['clientes'][0]['nombre'] );
                    $PartyLegalEntity->appendChild( $RegistrationName );

        /* sumatoria Impuestos */
        foreach ($comprobante['sumatoriasImpuestos'] as $value) {
            if ($value['monto'] != 0) {
                $TaxTotal = $xml->createElement( "cac:TaxTotal" );
                $invoice->appendChild( $TaxTotal );
                    $TaxAmount = $xml->createElement( "cbc:TaxAmount", $value['monto'] );
                    $TaxAmount->setAttribute("currencyID", "PEN");
                    $TaxTotal->appendChild( $TaxAmount );

                    $TaxSubtotal = $xml->createElement( "cac:TaxSubtotal" );
                    $TaxTotal->appendChild( $TaxSubtotal );
                        $TaxAmount = $xml->createElement( "cbc:TaxAmount", $value['monto'] );
                        $TaxAmount->setAttribute("currencyID", "PEN");
                        $TaxSubtotal->appendChild( $TaxAmount );

                        $TaxCategory = $xml->createElement( "cac:TaxCategory" );
                        $TaxSubtotal->appendChild( $TaxCategory );
                            $TaxScheme = $xml->createElement( "cac:TaxScheme" );
                            $TaxCategory->appendChild( $TaxScheme );
                                $ID = $xml->createElement( "cbc:ID", $value['codigo_tributo'] );
                                $TaxScheme->appendChild( $ID );

                                $Name = $xml->createElement( "cbc:Name", $value['nombre_tributo'] );
                                $TaxScheme->appendChild( $Name );

                                $TaxTypeCode = $xml->createElement( "cbc:TaxTypeCode", $value['ci_tributo'] );
                                $TaxScheme->appendChild( $TaxTypeCode );
            }
        }

        /* Importe Total */
        $LegalMonetaryTotal = $xml->createElement( "cac:LegalMonetaryTotal" );
        $invoice->appendChild( $LegalMonetaryTotal );
            $PayableAmount = $xml->createElement( "cbc:PayableAmount", $comprobante['importeTotal'] );
            $PayableAmount->setAttribute("currencyID", "PEN");
            $LegalMonetaryTotal->appendChild( $PayableAmount );
       
        /* Detalles */
        foreach ($comprobante['detalles'] as $detalle) {
            $InvoiceLine = $xml->createElement( "cac:InvoiceLine" );
            $invoice->appendChild( $InvoiceLine );
                $ID = $xml->createElement( "cbc:ID", $detalle['numero_item'] );
                $InvoiceLine->appendChild( $ID );

                $InvoicedQuantity = $xml->createElement( "cbc:InvoicedQuantity", $detalle['cantidad'] );
                $InvoicedQuantity->setAttribute("unitCode", $detalle['unidad_medida']);
                $InvoiceLine->appendChild( $InvoicedQuantity );

                $LineExtensionAmount = $xml->createElement( "cbc:LineExtensionAmount", $detalle['valor_venta'] );
                $LineExtensionAmount->setAttribute("currencyID", 'PEN');
                $InvoiceLine->appendChild( $LineExtensionAmount );

                $PricingReference = $xml->createElement( "cac:PricingReference");
                $InvoiceLine->appendChild( $PricingReference );
                    $AlternativeConditionPrice = $xml->createElement( "cac:AlternativeConditionPrice");
                    $PricingReference->appendChild( $AlternativeConditionPrice );
                        $PriceAmount = $xml->createElement( "cbc:PriceAmount", $detalle['precio_venta']['monto'] );
                        $PriceAmount->setAttribute("currencyID", 'PEN');
                        $AlternativeConditionPrice->appendChild( $PriceAmount );

                        $PriceTypeCode = $xml->createElement( "cbc:PriceTypeCode", $detalle['precio_venta']['codigo'] );
                        $AlternativeConditionPrice->appendChild( $PriceTypeCode );

                /* ISC por Item */
                if ( $detalle['afectacion_isc']['monto'] != 0 ) {
                    $TaxTotal = $xml->createElement( "cac:TaxTotal" );
                    $InvoiceLine->appendChild( $TaxTotal );
                        $TaxAmount = $xml->createElement( "cbc:TaxAmount", $detalle['afectacion_isc']['monto'] );
                        $TaxAmount->setAttribute("currencyID", "PEN");
                        $TaxTotal->appendChild( $TaxAmount );

                        $TaxSubtotal = $xml->createElement( "cac:TaxSubtotal" );
                        $TaxTotal->appendChild( $TaxSubtotal );
                            $TaxAmount = $xml->createElement( "cbc:TaxAmount", $detalle['afectacion_isc']['monto'] );
                            $TaxAmount->setAttribute("currencyID", "PEN");
                            $TaxSubtotal->appendChild( $TaxAmount );

                            $TaxCategory = $xml->createElement( "cac:TaxCategory" );
                            $TaxSubtotal->appendChild( $TaxCategory );
                                $TierRange = $xml->createElement( "cac:TierRange", $detalle['afectacion_isc']['codigo_tipo'] );
                                $TaxCategory->appendChild( $TierRange );

                                $TaxScheme = $xml->createElement( "cac:TaxScheme" );
                                $TaxCategory->appendChild( $TaxScheme );
                                    $ID = $xml->createElement( "cbc:ID", $detalle['afectacion_isc']['codigo_tributo'] );
                                    $TaxScheme->appendChild( $ID );

                                    $Name = $xml->createElement( "cbc:Name", $detalle['afectacion_isc']['nombre_tributo'] );
                                    $TaxScheme->appendChild( $Name );

                                    $TaxTypeCode = $xml->createElement( "cbc:TaxTypeCode", $detalle['afectacion_isc']['ci_tributo'] );
                                    $TaxScheme->appendChild( $TaxTypeCode );
                }

                /* IGV por Item */
                if ( $detalle['afectacion_igv']['monto'] != 0 ) {
                    $TaxTotal = $xml->createElement( "cac:TaxTotal" );
                    $InvoiceLine->appendChild( $TaxTotal );
                        $TaxAmount = $xml->createElement( "cbc:TaxAmount", $detalle['afectacion_igv']['monto'] );
                        $TaxAmount->setAttribute("currencyID", "PEN");
                        $TaxTotal->appendChild( $TaxAmount );

                        $TaxSubtotal = $xml->createElement( "cac:TaxSubtotal" );
                        $TaxTotal->appendChild( $TaxSubtotal );
                            $TaxAmount = $xml->createElement( "cbc:TaxAmount", $detalle['afectacion_igv']['monto'] );
                            $TaxAmount->setAttribute("currencyID", "PEN");
                            $TaxSubtotal->appendChild( $TaxAmount );

                            $TaxCategory = $xml->createElement( "cac:TaxCategory" );
                            $TaxSubtotal->appendChild( $TaxCategory );
                                $TaxExemptionReasonCode = $xml->createElement( "cac:TaxExemptionReasonCode", $detalle['afectacion_igv']['codigo_tipo'] );
                                $TaxCategory->appendChild( $TaxExemptionReasonCode );

                                $TaxScheme = $xml->createElement( "cac:TaxScheme" );
                                $TaxCategory->appendChild( $TaxScheme );
                                    $ID = $xml->createElement( "cbc:ID", $detalle['afectacion_igv']['codigo_tributo'] );
                                    $TaxScheme->appendChild( $ID );

                                    $Name = $xml->createElement( "cbc:Name", $detalle['afectacion_igv']['nombre_tributo'] );
                                    $TaxScheme->appendChild( $Name );

                                    $TaxTypeCode = $xml->createElement( "cbc:TaxTypeCode", $detalle['afectacion_igv']['ci_tributo'] );
                                    $TaxScheme->appendChild( $TaxTypeCode );
                }

                $Item = $xml->createElement( "cac:Item" );
                $InvoiceLine->appendChild( $Item );
                    $Description = $xml->createElement( "cbc:Description", $detalle['descripcion'] );
                    $Item->appendChild( $Description );

                    if ($detalle['codigo_producto'] != null) {
                        $SellersItemIdentification = $xml->createElement( "cac:SellersItemIdentification" );
                        $Item->appendChild( $SellersItemIdentification );
                            $ID = $xml->createElement( "cbc:ID", $detalle['codigo_producto'] );
                            $SellersItemIdentification->appendChild( $ID );
                    }
                    
                $Price = $xml->createElement( "cac:Price" );
                $InvoiceLine->appendChild( $Price );
                    $PriceAmount = $xml->createElement( "cbc:PriceAmount", $detalle['valor_unitario'] );
                    $PriceAmount->setAttribute("currencyID", "PEN");
                    $Price->appendChild( $PriceAmount );
        }
        
        $strings_xml = $xml->saveXML();
        $xml->save('xml_files/pruebal.xml'); 

        //dd($comprobante);
        //dd($strings_xml);
    }

    public function crearNumeracion( $tipoComprobante )
    {
        $data = Comprobante::where( 'tipo_comprobante', $tipoComprobante )->get();
        $total = count($data);

        $numeroSiguiente = $total + 1;

        if ($numeroSiguiente > 99999999) 
            $numero = ($numeroSiguiente % 100000000) + 1;
        else
            $numero = ($numeroSiguiente % 100000000);

        $serie = floor(($numeroSiguiente / 100000000)) + 1;
        $serie = ($serie < 10) ? "0" . $serie : $serie;

        $numeracion = "FA" . $serie . "-" . $numero;

        return $numeracion;
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
