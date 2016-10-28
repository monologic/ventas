app.controller('facturaController', function($scope, $http, tipoDocumento, unidadesDeMedida) {

    $scope.documentoTipos = tipoDocumento;
    $scope.tipo_doc = $scope.documentoTipos[3];
    $scope.unidadesDeMedida = unidadesDeMedida;
    $scope.unidad_medida = $scope.unidadesDeMedida[0];

    $scope.Factura = {};
    $scope.detalles = [];

    $scope.searchDocumento = function (numero) {
        if (numero != undefined) {
            $http.get('getDocumento/' + numero).then(function successCallback(response) {
                if (response.data.hasOwnProperty('tipo_doc')) {
                    $('#tipo_doc').val(response.data.tipo_doc);
                    $scope.nombre = response.data.clientes[0].nombre;
                    $scope.nuevoClientebtn = false;

                    $scope.Factura.cliente = response.data;
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
                'nombre':$scope.nombre
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

        if ($scope.Producto.tasa_isc != null)
            afectacion_isc = $scope.Producto.valor_unitario * $scope.Producto.tasa_isc * $scope.cantidad;
        else
            afectacion_isc = 0;

        if ($scope.Producto.codigo != "")
            codigo_prod = $scope.Producto.codigo;
        else 
            codigo_prod = null;

        $scope.detalles.push({
            unidad_medida: $scope.unidad_medida, 
            cantidad: $scope.cantidad,
            descripcion: $scope.Producto.descripcion,
            valor_unitario:$scope.Producto.valor_unitario,
            precio_venta:{ 
                monto: $scope.Producto.precio_venta,
                codigo: '01',
            },
            afectacion_igv: {
                monto: ($scope.Producto.valor_unitario  * $scope.cantidad + afectacion_isc) * $scope.Producto.tasa_igv,
                codigo_tipo: '10',
                codigo_tributo: '1000',
                nombre_tributo: 'VAT'
            },
            afectacion_isc: {
                monto: afectacion_isc,
                codigo_tipo: $scope.Producto.cod_tipo_sistema_isc,
                codigo_tributo: '2000',
                nombre_tributo: 'EXC'
            },
            valor_venta: $scope.Producto.valor_unitario  * $scope.cantidad,
            codigo_producto: codigo_prod,
            numero_item: $scope.detalles.length + 1
        });

        console.log($scope.detalles);

        $scope.calcularTotalValorVenta();
    }

    $scope.calcularTotalValorVenta = function () {

        var totalValorVenta = 0;
        for ( i in $scope.detalles) {
            totalValorVenta += $scope.detalles[i].valor_venta;
        }
        $scope.Factura.totalValorVenta = totalValorVenta;

        $scope.calcularTotalIgv();
    }

    $scope.calcularTotalIgv = function () {

        var totalIgv = 0;
        for ( i in $scope.detalles) {
            totalIgv += $scope.detalles[i].afectacion_igv.monto;
        }
        $scope.Factura.totalIgv = totalIgv;

        $scope.calcularTotalIsc();
    }

    $scope.calcularTotalIsc = function () {

        var totalIsc = 0;
        for ( i in $scope.detalles) {
            totalIsc += $scope.detalles[i].afectacion_isc.monto;
        }
        $scope.Factura.totalIsc = totalIsc;

        $scope.calcularImporteTotal();
    }

    $scope.calcularImporteTotal = function () {
        $scope.Factura.importeTotal = $scope.Factura.totalValorVenta + $scope.Factura.totalIsc + $scope.Factura.totalIgv;
    }

    $scope.store = function () {
        $http.post('../facturas',
            {   'descripcion':$scope.descripcion,
                'valor_unitario':$scope.val_unit,
                'codigo':$scope.codigo
            }).then(function successCallback(response) {
                $scope.clean();

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
});