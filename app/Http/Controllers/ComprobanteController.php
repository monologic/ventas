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

        /*

        <cac:Signature>
            <cbc:ID>IDSignSP</cbc:ID>
            <cac:SignatoryParty>
                <cac:PartyIdentification>
                    <cbc:ID>20100454523</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                    <cbc:Name>SOPORTE TECNOLOGICO EIRL</cbc:Name>
                </cac:PartyName>
            </cac:SignatoryParty>
            <cac:DigitalSignatureAttachment>
                <cac:ExternalReference>
                    <cbc:URI>#SignatureSP</cbc:URI>
                </cac:ExternalReference>
            </cac:DigitalSignatureAttachment>
        </cac:Signature>
        
        <ds:Signature Id="SignatureCF">
            <ds:SignedInfo>
                <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
                <ds:Reference URI="">
                    <ds:Transforms>
                        <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#envelopedsignature"/>
                    </ds:Transforms>
                    <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                    <ds:DigestValue>ZYhfRQAjGQ4oOf0a+ryuqbuG6bc=</ds:DigestValue>
                </ds:Reference>
            </ds:SignedInfo>
            <ds:SignatureValue>
                dAsw7ytlJGtxSIWPeVSuN8M8AwjoHVjY3cy9N/3hyTH/Pod7km+WRx52aWEBrGaMc1W4i5IQZFZsToqoUHXueC3k9SBt94xPEhT2331V8qQsJqCMdW0U5NpZnyoebL8MPISLF12z869TnDlpFrbDuqY+rPqSueQHyTlhtkVWDVI=
            </ds:SignatureValue>
            <ds:KeyInfo>
                <ds:X509Data>
                    <ds:X509SubjectName>
                        1.2.840.113549.1.9.1=#161a4253554c434140534f55544845524e504552552e434f4d2e5045,CN=JuanRobles,OU=20200464529,O=MAYORISTAS CFFSA,L=LIMA,ST=LIMA,C=PE
                    </ds:X509SubjectName>
                    <ds:X509Certificate>
                        MIIESTCCAzGgAwIBAgIKWOCRzgAAAAAAIjANBgkqhkiG9w0BAQUFADAnMRUwEwYKCZImiZPyLGQBGRYFU1VOQVQxDjAMBgNVBAMTBVNVTkFUMB4XDTEwMTIyODE5NTExMFoXDTExMTIyODIwMDExMFowgZUxCzAJBgNVBAYTAlBFMQ0wCwYDVQQIEwRMSU1BMQ0wCwYDVQQHEwRMSU1BMREwDwYDVQQKEwhTT1VUSEVSTjEUMBIGA1UECxMLMjAxMDAxNDc1MTQxFDASBgNVBAMTC0JvcmlzIFN1bGNhMSkwJwYJKoZIhvcNAQkBFhpCU1VMQ0FAU09VVEhFUk5QRVJVLkNPTS5QRTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAtRtcpfBLzyajuEmYt4mVH8EE02KQiETsdKStUThVYM7g3Lkx5zq3SH5nLH00EKGCtota6RR+V40sgIbnh+Nfs1SOQcAohNwRfWhho7sKNZFR971rFxj4cTKMEvpt8Dr98UYFkJhph6WnsniGM2tJDq9KJ52UXrlScMfBityx0AsCAwEAAaOCAYowggGGMA4GA1UdDwEB/wQEAwIE8DBEBgkqhkiG9w0BCQ8ENzA1MA4GCCqGSIb3DQMCAgIAgDAOBggqhkiG9w0DBAICAIAwBwYFKw4DAgcwCgYIKoZIhvcNAwcwHQYDVR0OBBYEFG/m6twbiRNzRINavjq+U0j/sZECMBMGA1UdJQQMMAoGCCsGAQUFBwMCMB8GA1UdIwQYMBaAFN9kHQDqWONmozw3xdNSIMFW2t+7MFkGA1UdHwRSMFAwTqBMoEqGImh0dHA6Ly9wY2IyMjYvQ2VydEVucm9sbC9TVU5BVC5jcmyGJGZpbGU6Ly9cXHBjYjIyNlxDZXJ0RW5yb2xsXFNVTkFULmNybDB+BggrBgEFBQcBAQRyMHAwNQYIKwYBBQUHMAKGKWh0dHA6Ly9wY2IyMjYvQ2VydEVucm9sbC9wY2IyMjZfU1VOQVQuY3J0MDcGCCsGAQUFBzAChitmaWxlOi8vXFxwY2IyMjZcQ2VydEVucm9sbFxwY2IyMjZfU1VOQVQuY3J0MA0GCSqGSIb3DQEBBQUAA4IBAQBI6wJ/QmRpz3C3rorBflOvA9DOa3GNiiB7rtPIjF4mPmtgfo2pK9gvnxmV2pST3ovfu0nbG2kpjzzaaelRjEodHvkcM3abGsOE53wfxqQF5uf/jkzZA9hbLHtE1aLKBD0Mhzc6cvI072alnE6QU3RZ16ie9CYsHmMrs+sPHMy8DJU5YrdnqHdSn2D3nhKBi4QfT/WURPOuo6DF4iWgrCyMf3eJgmGKSUN3At5fK4HSpfyURT0kboaJKNBgQwy0HhGh5BLM7DsTi/KwfdUYkoFgrY71Pm23+ra+xTow1Vk9gj5NqrlpMY5gAVQXEIo1++GxDtaK/5EiVKSqzJ6geIfz
                    </ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </ds:Signature>

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
