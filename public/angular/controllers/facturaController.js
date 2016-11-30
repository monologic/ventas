app.controller('facturaController', function($scope, $http, tipoDocumento, unidadesDeMedida) {

    $scope.documentoTipos = tipoDocumento;
    $scope.tipo_doc = $scope.documentoTipos[3];

    $scope.Factura = {};
    $scope.Factura.tipoDocumento = "01";
    //$scope.Factura.detraccion = {};
    $scope.detalles = [];



    $scope.searchDocumento = function (numero) {
        if (numero != undefined) {
            $http.get('getDocumento/' + numero).then(function successCallback(response) {
                if (response.data.hasOwnProperty('tipo_doc')) {
                    $('#tipo_doc').val(response.data.tipo_doc);
                    $scope.nombre = response.data.clientes[0].nombre;
                    $scope.nuevoClientebtn = false;

                    $scope.Factura.cliente = response.data;
                    if ($scope.Factura.cliente.clientes[0].agente_retencion == 1)
                        swal('', 'El cliente es agente de Retención', 'success');
                }
                else{
                    $scope.nombre = "";
                    $scope.nuevoClientebtn = true;
                }
                
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
        }
    }

    $scope.storeCliente = function () {
        $http.post('../identidadDocumentos',
            {   'numero':$scope.numero_di,
                'tipo_doc':$scope.tipo_doc.codigo,
                'nombre':$scope.nombre,
                'agente_percep':$('#agente_percep').prop('checked'),
                'agente_retencion':$('#agente_retencion').prop('checked')
            }).then(function successCallback(response) {
                if (response.data.hasOwnProperty('tipo_doc')) {
                    swal('', 'Se ha guardado el nuevo Cliente', 'success');
                    $scope.nuevoClientebtn = false;
                }
                else
                    swal('', 'El número de documento de identidad ya está registrado', 'error');

                $scope.Factura.cliente = response.data;

            }, function errorCallback(response) {
                
                 swal('', 'Algo anda mal :(', 'error');
            
            });
    }

    $scope.getProductos = function () {
        $http.get('../productos').then(function successCallback(response) {
                $scope.productosAll = response.data;
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    $scope.getInformation = function () {
        $http.get('../information').then(function successCallback(response) {
                $scope.Factura.information = response.data;
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    $scope.mostrarLista = function () {
        if ($scope.productoBuscar == "")
            $scope.activarListado = false;
        else
            $scope.activarListado = true;
    }

    $scope.assignValueAndHide = function (data) {
        $scope.Producto = data;
        $scope.productoBuscar = data.descripcion;
        $scope.activarListado = false;
    }

    $scope.addDetalle = function () {
       
        if ($scope.Producto.afectacion_igv == 'Gravado') {
            $scope.calcDetalleGravado();
        }
        else{
            $scope.calcDetalleExoIna();
        }

        $scope.calcularTotalValorVenta();
    }

    $scope.calcDetalleGravado = function () {
        if ( $scope.Producto.tasa_isc != null )
            afectacion_isc = $scope.Producto.valor_unitario * $scope.Producto.tasa_isc * $scope.cantidad;
        else
            afectacion_isc = 0;

        if ($scope.Producto.codigo != "")
            codigo_prod = $scope.Producto.codigo;
        else 
            codigo_prod = null;

        $scope.detalles.push({
            idProducto: $scope.Producto.id,
            percepcion: $scope.Producto.tasa_percep,
            detraccion: $scope.Producto.tasa_detracc,
            afectacion: $scope.Producto.afectacion_igv,
            unidad_medida: $scope.Producto.unidad_medida, 
            cantidad: $scope.cantidad,
            descripcion: $scope.Producto.descripcion,
            valor_unitario:$scope.Producto.valor_unitario,
            precio_venta:{ 
                monto: $scope.Producto.precio_venta,
                codigo: '01',
            },
            afectacion_igv: {
                monto: ($scope.Producto.valor_unitario  * $scope.cantidad + afectacion_isc) * $scope.Factura.information.igv,
                codigo_tipo: '10',
                codigo_tributo: '1000',
                nombre_tributo: 'IGV',
                ci_tributo: 'VAT'
            },
            afectacion_isc: {
                monto: afectacion_isc,
                codigo_tipo: $scope.Producto.cod_tipo_sistema_isc,
                codigo_tributo: '2000',
                nombre_tributo: 'ISC',
                ci_tributo: 'EXC'
            },
            valor_venta: $scope.Producto.valor_unitario  * $scope.cantidad,
            codigo_producto: codigo_prod,
            numero_item: $scope.detalles.length + 1
        });

        $scope.Factura.detalles = $scope.detalles;
    }

    $scope.calcDetalleExoIna = function () {
        if ( $scope.Producto.tasa_isc != null )
            afectacion_isc = $scope.Producto.valor_unitario * $scope.Producto.tasa_isc * $scope.cantidad;
        else
            afectacion_isc = 0;

        if ($scope.Producto.codigo != "")
            codigo_prod = $scope.Producto.codigo;
        else 
            codigo_prod = null;

        $scope.detalles.push({
            percepcion: $scope.Producto.tasa_percep,
            detraccion: $scope.Producto.tasa_detracc,
            afectacion: $scope.Producto.afectacion_igv,
            unidad_medida: $scope.unidad_medida, 
            cantidad: $scope.cantidad,
            descripcion: $scope.Producto.descripcion,
            valor_unitario:$scope.Producto.valor_unitario,
            precio_venta:{ 
                monto: $scope.Producto.precio_venta,
                codigo: '01',
            },
            afectacion_isc: {
                monto: afectacion_isc,
                codigo_tipo: $scope.Producto.cod_tipo_sistema_isc,
                codigo_tributo: '2000',
                nombre_tributo: 'ISC',
                ci_tributo: 'EXC'
            },
            valor_venta: $scope.Producto.valor_unitario  * $scope.cantidad,
            codigo_producto: codigo_prod,
            numero_item: $scope.detalles.length + 1
        });

        $scope.Factura.detalles = $scope.detalles;
    }


    $scope.calcularTotalValorVenta = function () {

        var totalGravado = 0;
        var totalExonerado = 0;
        var totalInafecto = 0;

        for (i in $scope.detalles) {
            if ($scope.detalles[i].afectacion == "Gravado") {
                totalGravado += $scope.detalles[i].valor_venta;
            }
            if ($scope.detalles[i].afectacion == "Exonerado") {
                totalExonerado += $scope.detalles[i].valor_venta;
            }
            if ($scope.detalles[i].afectacion == "Inafecto") {
                totalInafecto += $scope.detalles[i].valor_venta;
            }
        }
        $scope.Factura.totalValorVenta = [
            {
            codigo: '1001',
            monto: totalGravado
            },
            {
            codigo: '1002',
            monto: totalExonerado
            },
            {
            codigo: '1003',
            monto: totalInafecto
            }
        ]

        $scope.calcularTotalIgv();
    }

    $scope.calcularTotalIgv = function () {

        var totalIgv = 0;
        for ( i in $scope.detalles) {
            if ($scope.detalles[i].afectacion == "Gravado")
                totalIgv += $scope.detalles[i].afectacion_igv.monto;
        }
        $scope.Factura.totalIgv = {
            monto: totalIgv,
            codigo_tributo: '1000',
            nombre_tributo: 'IGV',
            ci_tributo: 'VAT'
        }

        $scope.Factura.sumatoriasImpuestos = [];
        $scope.Factura.sumatoriasImpuestos.push($scope.Factura.totalIgv);

        $scope.calcularTotalIsc();
    }


    $scope.calcularTotalIsc = function () {

        var totalIsc = 0;
        for ( i in $scope.detalles) {
            totalIsc += $scope.detalles[i].afectacion_isc.monto;
        }
        $scope.Factura.totalIsc = {
            monto: totalIsc,
            codigo_tributo: '2000',
            nombre_tributo: 'ISC',
            ci_tributo: 'EXC'
        }

        $scope.Factura.sumatoriasImpuestos.push($scope.Factura.totalIsc);

        $scope.calcularImporteTotal();
    }

    $scope.calcularImporteTotal = function () {
        $scope.Factura.importeTotal = $scope.Factura.totalValorVenta[0].monto + $scope.Factura.totalValorVenta[1].monto + $scope.Factura.totalValorVenta[2].monto + $scope.Factura.totalIsc.monto + $scope.Factura.totalIgv.monto;
        //console.log($scope.Factura);
        $scope.calcularDetraccion();
    }

    $scope.calcularDetraccion = function () {
        $scope.Factura.detraccion = [];
        for (i in $scope.detalles) {
            if ($scope.detalles[i].detraccion != null) {
                
                total = $scope.detalles[i].valor_venta + $scope.detalles[i].afectacion_igv.monto + $scope.detalles[i].afectacion_isc.monto;
                if (total > 700) {
                    montoDetraccion = total * $scope.detalles[i].detraccion;
                    monto = {
                        codigo: "2003",
                        porcentaje: $scope.detalles[i].detraccion,
                        monto: montoDetraccion
                    }
                    $scope.Factura.detraccion.push(monto);
                }
            }
        }  
        
        if ($scope.Factura.detraccion.length == 0) {
            delete $scope.Factura.detraccion;
            $scope.calcularPercepcion();
        }
        else{
            $scope.divDetraccion = true;
            $scope.totalDetracc = 0;
            porcentaje = $scope.Factura.detraccion[0].porcentaje;
            a = 0;
            for (var i = 0; i < $scope.Factura.detraccion.length; i++) {
                $scope.totalDetracc += $scope.Factura.detraccion[i].monto;
                if (porcentaje == $scope.Factura.detraccion[i].porcentaje)
                    a++;
            }
            if (a == $scope.Factura.detraccion.length) {
                porcentaje = (parseFloat(porcentaje) * 100).toFixed(2);
                porcentaje += "%";
                $scope.Factura.detraccionTotal = {
                    codigo: "2003",
                    porcentaje: porcentaje,
                    monto: $scope.totalDetracc,
                    numero_cuenta: ""
                }
            }
            else {
                $scope.Factura.detraccionTotal = {
                    codigo: "2003",
                    monto: $scope.totalDetracc,
                    numero_cuenta: ""
                }
            }

        }

    }

    $scope.calcularPercepcion = function () {

        clienteAgentePercep = $scope.Factura.cliente.clientes[0].agente_percep; 
        clienteAgenteRetencion = $scope.Factura.cliente.clientes[0].agente_retencion;

        if ($scope.Factura.information.agente_percep == 1) {

            var base_imponible = 0;
            var totalPercepcion = 0;

            

            if (clienteAgenteRetencion == 0) {
                if (clienteAgentePercep == 0 || clienteAgentePercep == null) {
                    for (i in $scope.detalles) {
                        if ($scope.detalles[i].afectacion == "Gravado") {
                            //Calculando Percepcion por Item
                            if ($scope.detalles[i].percepcion != null) {
                                total = $scope.detalles[i].valor_venta + $scope.detalles[i].afectacion_igv.monto + $scope.detalles[i].afectacion_isc.monto;
                                base_imponible += total;
                                montoPercep = total * $scope.detalles[i].percepcion;
                                totalPercepcion += montoPercep;
                            }
                        }
                    }
                }
                else {
                    for (i in $scope.detalles) {

                        if ($scope.detalles[i].afectacion == "Gravado") {
                            console.log($scope.detalles[i]);
                            if ($scope.detalles[i].percepcion != null) {
                                total = $scope.detalles[i].valor_venta + $scope.detalles[i].afectacion_igv.monto + $scope.detalles[i].afectacion_isc.monto;
                                base_imponible += total;
                                montoPercep = total * 0.005;
                                totalPercepcion += montoPercep;
                            }
                        }
                    }
                }

                if (totalPercepcion != 0) {
                    $scope.divPercepcion = true;
                    $scope.Factura.percepcion = {
                        codigo: "2001",
                        base_imponible: base_imponible,
                        monto: totalPercepcion,
                        monto_total: $scope.Factura.importeTotal + totalPercepcion
                    }
                }
            }
        }
        else if(clienteAgenteRetencion == 1) {
            $scope.calcularRetencion();
        }

    }

    $scope.calcularRetencion = function () {
       
        agentePercep = $scope.Factura.information.agente_percep;
        agenteRetencion = $scope.Factura.information.agente_retencion;

        if (agenteRetencion == 0) {

            importeGravado = $scope.Factura.totalValorVenta[0].monto + $scope.Factura.totalIsc.monto + $scope.Factura.totalIgv.monto;
            monto = importeGravado * 0.03;
            importeTotal = $scope.Factura.importeTotal - monto;

            $scope.Factura.retencion = {
                codigo: "2002",
                importeGravado: importeGravado,
                monto:  monto,
                importeTotal:importeTotal,
            }
            console.log($scope.Factura.retencion);
        }
    }

    $scope.addDataDetraccion = function () {

        $scope.Factura.detraccionTotal.numero_cuenta = $('#numero_cuenta').val();

        //console.log($scope.Factura);
    }

    $scope.terminarFactura = function () {
        if ($scope.Factura.hasOwnProperty('cliente')) {
            if ($scope.Factura.hasOwnProperty('detalles')) {
                $scope.Factura.moneda = 'PEN';
                $scope.Factura.version_UBL = '2.0';
                $scope.Factura.version_doc = '1.0';

                monto_letras = covertirNumLetras($scope.Factura.importeTotal+"");
                monto_letras.trim();
                $scope.Factura.leyendas = [];
                $scope.Factura.leyendas.push({
                    codigo: "1000",
                    value: monto_letras
                });
                //alert(JSON.stringify($scope.Factura));
                $scope.store(JSON.stringify($scope.Factura));
            }
            else
                swal("Faltan datos!", "La factura debería tener por lo menos un detalle.", "warning");
        }
        else
            swal("Faltan datos!", "Falta información del cliente.", "warning");
    }

    $scope.store = function (facturaJson) {
        $http.post('../comprobante',
            {   'json': facturaJson
            }).then(function successCallback(response) {
                //$scope.clean();

                swal({  title: "Perfecto!",
                        text: "Ha creado un nuevo registro",
                        type: "success",   
                        showCancelButton: true,
                        cancelButtonText: "Seguir creando",
                        confirmButtonText: "Ver todos",   
                        closeOnConfirm: true
                    },
                    function(){
                        window.location.href = '#/Factura';
                    });
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    $scope.clean = function () {
        $scope.descripcion = "";
        $scope.val_unit = "";
        $scope.codigo = "";
    }

    $scope.get = function () {
        $http.get('facturas').then(function successCallback(response) {
                $scope.data = response.data;
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    $scope.dataUpdate = function (data) {
        $scope.id = data.id;
        $scope.descripcion = data.descripcion;
        //$scope.valor_unitario = data.valor_unitario;
        $('#valor_unitario').val(data.valor_unitario);
        $scope.codigo = data.codigo;
    }

    $scope.update = function () {
        $http.put('facturas/' + $scope.id,
            {   'descripcion':$scope.descripcion,
                'valor_unitario':$scope.valor_unitario,
                'codigo':$scope.codigo
            }).then(function successCallback(response) {
                swal("Editado!", 
                    "El registro se ha editado.", 
                    "success");
                $('#updateModal').modal('toggle');
                $scope.data = response.data;
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    function mod(dividendo , divisor) 
    { 
      resDiv = dividendo / divisor ;  
      parteEnt = Math.floor(resDiv);            // Obtiene la parte Entera de resDiv 
      parteFrac = resDiv - parteEnt ;      // Obtiene la parte Fraccionaria de la división
      //modulo = parteFrac * divisor;  // Regresa la parte fraccionaria * la división (modulo) 
      modulo = Math.round(parteFrac * divisor)
      return modulo; 
    } // Fin de función mod

    // Función ObtenerParteEntDiv, regresa la parte entera de una división
    function ObtenerParteEntDiv(dividendo , divisor) 
    { 
      resDiv = dividendo / divisor ;  
      parteEntDiv = Math.floor(resDiv);
      return parteEntDiv; 
    } // Fin de función ObtenerParteEntDiv

    // function fraction_part, regresa la parte Fraccionaria de una cantidad
    function fraction_part(dividendo , divisor) 
    { 
      resDiv = dividendo / divisor ;  
      f_part = Math.floor(resDiv); 
      return f_part; 
    } // Fin de función fraction_part


    // function string_literal conversion is the core of this program 
    // converts numbers to spanish strings, handling the general special 
    // cases in spanish language. 
    function string_literal_conversion(number) 
    {   
       // first, divide your number in hundreds, tens and units, cascadig 
       // trough subsequent divisions, using the modulus of each division 
       // for the next. 

       centenas = ObtenerParteEntDiv(number, 100); 
       
       number = mod(number, 100); 

       decenas = ObtenerParteEntDiv(number, 10); 
       number = mod(number, 10); 

       unidades = ObtenerParteEntDiv(number, 1); 
       number = mod(number, 1);  
       string_hundreds="";
       string_tens="";
       string_units="";
       // cascade trough hundreds. This will convert the hundreds part to 
       // their corresponding string in spanish.
       if(centenas == 1){
          string_hundreds = "ciento ";
       } 
       
       
       if(centenas == 2){
          string_hundreds = "doscientos ";
       }
        
       if(centenas == 3){
          string_hundreds = "trescientos ";
       } 
       
       if(centenas == 4){
          string_hundreds = "cuatrocientos ";
       } 
       
       if(centenas == 5){
          string_hundreds = "quinientos ";
       } 
       
       if(centenas == 6){
          string_hundreds = "seiscientos ";
       } 
       
       if(centenas == 7){
          string_hundreds = "setecientos ";
       } 
       
       if(centenas == 8){
          string_hundreds = "ochocientos ";
       } 
       
       if(centenas == 9){
          string_hundreds = "novecientos ";
       } 
       
     // end switch hundreds 

       // casgade trough tens. This will convert the tens part to corresponding 
       // strings in spanish. Note, however that the strings between 11 and 19 
       // are all special cases. Also 21-29 is a special case in spanish. 
       if(decenas == 1){
          //Special case, depends on units for each conversion
          if(unidades == 1){
             string_tens = "once";
          }
          
          if(unidades == 2){
             string_tens = "doce";
          }
          
          if(unidades == 3){
             string_tens = "trece";
          }
          
          if(unidades == 4){
             string_tens = "catorce";
          }
          
          if(unidades == 5){
             string_tens = "quince";
          }
          
          if(unidades == 6){
             string_tens = "dieciseis";
          }
          
          if(unidades == 7){
             string_tens = "diecisiete";
          }
          
          if(unidades == 8){
             string_tens = "dieciocho";
          }
          
          if(unidades == 9){
             string_tens = "diecinueve";
          }
       } 
       //alert("STRING_TENS ="+string_tens);
       
       if(decenas == 2){
          string_tens = "veinti";

       }
       if(decenas == 3){
          string_tens = "treinta";
       }
       if(decenas == 4){
          string_tens = "cuarenta";
       }
       if(decenas == 5){
          string_tens = "cincuenta";
       }
       if(decenas == 6){
          string_tens = "sesenta";
       }
       if(decenas == 7){
          string_tens = "setenta";
       }
       if(decenas == 8){
          string_tens = "ochenta";
       }
       if(decenas == 9){
          string_tens = "noventa";
       }
       
        // Fin de swicth decenas


       // cascades trough units, This will convert the units part to corresponding 
       // strings in spanish. Note however that a check is being made to see wether 
       // the special cases 11-19 were used. In that case, the whole conversion of 
       // individual units is ignored since it was already made in the tens cascade. 

       if (decenas == 1) 
       { 
          string_units="";  // empties the units check, since it has alredy been handled on the tens switch 
       } 
       else 
       { 
          if(unidades == 1){
             string_units = "un";
          }
          if(unidades == 2){
             string_units = "dos";
          }
          if(unidades == 3){
             string_units = "tres";
          }
          if(unidades == 4){
             string_units = "cuatro";
          }
          if(unidades == 5){
             string_units = "cinco";
          }
          if(unidades == 6){
             string_units = "seis";
          }
          if(unidades == 7){
             string_units = "siete";
          }
          if(unidades == 8){
             string_units = "ocho";
          }
          if(unidades == 9){
             string_units = "nueve";
          }
           // end switch units 
       } // end if-then-else 
       

    //final special cases. This conditions will handle the special cases which 
    //are not as general as the ones in the cascades. Basically four: 

    // when you've got 100, you dont' say 'ciento' you say 'cien' 
    // 'ciento' is used only for [101 >= number > 199] 
    if (centenas == 1 && decenas == 0 && unidades == 0) 
    { 
       string_hundreds = "cien " ; 
    }  

    // when you've got 10, you don't say any of the 11-19 special 
    // cases.. just say 'diez' 
    if (decenas == 1 && unidades ==0) 
    { 
       string_tens = "diez " ; 
    } 

    // when you've got 20, you don't say 'veinti', which is used 
    // only for [21 >= number > 29] 
    if (decenas == 2 && unidades ==0) 
    { 
      string_tens = "veinte " ; 
    } 

    // for numbers >= 30, you don't use a single word such as veintiuno 
    // (twenty one), you must add 'y' (and), and use two words. v.gr 31 
    // 'treinta y uno' (thirty and one) 
    if (decenas >=3 && unidades >=1) 
    { 
       string_tens = string_tens+" y "; 
    } 

    // this line gathers all the hundreds, tens and units into the final string 
    // and returns it as the function value.
    final_string = string_hundreds+string_tens+string_units;


    return final_string ; 

    } //end of function string_literal_conversion()================================ 

    // handle some external special cases. Specially the millions, thousands 
    // and hundreds descriptors. Since the same rules apply to all number triads 
    // descriptions are handled outside the string conversion function, so it can 
    // be re used for each triad. 


    function covertirNumLetras(number)
    {
       
      //number = number_format (number, 2);
       number1=number; 
       //settype (number, "integer");
       cent = number1.split(".");   
       centavos = cent[1];
       //Mind Mod
       number=cent[0];
       
       if (centavos == 0 || centavos == undefined)
       {
        centavos = "00";
       }

       if (number == 0 || number == "") 
       { // if amount = 0, then forget all about conversions, 
          centenas_final_string=" cero "; // amount is zero (cero). handle it externally, to 
          // function breakdown 
      } 
       else 
       { 
       
         millions  = ObtenerParteEntDiv(number, 1000000); // first, send the millions to the string 
          number = mod(number, 1000000);           // conversion function 
          
         if (millions != 0)
          {                      
          // This condition handles the plural case 
             if (millions == 1) 
             {              // if only 1, use 'millon' (million). if 
                descriptor= " millon ";  // > than 1, use 'millones' (millions) as 
                } 
             else 
             {                           // a descriptor for this triad. 
                  descriptor = " millones "; 
                } 
          } 
          else 
          {    
             descriptor = " ";                 // if 0 million then use no descriptor. 
          } 
          millions_final_string = string_literal_conversion(millions)+descriptor; 
              
          
          thousands = ObtenerParteEntDiv(number, 1000);  // now, send the thousands to the string 
            number = mod(number, 1000);            // conversion function. 
          //print "Th:".thousands;
         if (thousands != 1) 
          {                   // This condition eliminates the descriptor 
             thousands_final_string =string_literal_conversion(thousands) + " mil "; 
           //  descriptor = " mil ";          // if there are no thousands on the amount 
          } 
          if (thousands == 1)
          {
             thousands_final_string = " mil "; 
         }
          if (thousands < 1) 
          { 
             thousands_final_string = " "; 
          } 
      
          // this will handle numbers between 1 and 999 which 
          // need no descriptor whatsoever. 

         centenas  = number;                     
          centenas_final_string = string_literal_conversion(centenas) ; 
          
       } //end if (number ==0) 

       /*if (ereg("un",centenas_final_string))
       {
         centenas_final_string = ereg_replace("","o",centenas_final_string); 
       }*/
       //finally, print the output. 

       /* Concatena los millones, miles y cientos*/
       cad = millions_final_string+thousands_final_string+centenas_final_string; 
       
       /* Convierte la cadena a Mayúsculas*/
       cad = cad.toUpperCase();       

       if (centavos.length>2)
       {  
        
          if(centavos.substring(2,3)>= 5){
             centavos = centavos.substring(0,1)+(parseInt(centavos.substring(1,2))+1).toString();
          }   else{
          
            centavos = centavos.substring(0,1);
          }
       }

       /* Concatena a los centavos la cadena "/100" */
       if (centavos.length==1)
       {
          centavos = centavos+"0";
       }
       centavos = centavos+ "/100"; 


       /* Asigna el tipo de moneda, para 1 = PESO, para distinto de 1 = PESOS*/
       if (number == 1)
       {
          moneda = " Y ";  
       }
       else
       {
          moneda = " Y ";  
       }
       /* Regresa el número en cadena entre paréntesis y con tipo de moneda y la fase M.N.*/
       //Mind Mod, si se deja MIL pesos y se utiliza esta función para imprimir documentos
       //de caracter legal, dejar solo MIL es incorrecto, para evitar fraudes se debe de poner UM MIL pesos
       if(cad == '  MIL ')
       {
        cad=' UN MIL ';
       }
       
       //alert( "FINAL="+cad+moneda+centavos+" NUEVOS SOLES");
       return cad+moneda+centavos+" NUEVOS SOLES";
    }
});