app.controller('facturaController', function($scope, $http, tipoDocumento) {

    $scope.documentoTipos = tipoDocumento;
    $scope.tipo_doc = $scope.documentoTipos[3];

    $scope.searchDocumento = function (numero) {
        if (numero != undefined) {
            $http.get('getDocumento/' + numero).then(function successCallback(response) {
                if (response.data.hasOwnProperty('tipo_doc')) {
                    $('#tipo_doc').val(response.data.tipo_doc);
                    $scope.nombre = response.data.clientes[0].nombre;
                    $scope.nuevoClientebtn = false;
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
                if (response.data) {
                    swal('', 'Se ha guardado el nuevo Cliente', 'success');
                    $scope.nuevoClientebtn = false;
                }
                else
                    swal('', 'El número de documento de identidad ya está registrado', 'error');
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

    $scope.mostrarLista = function () {
        if ($scope.productoBuscar == "")
            $scope.activarListado = false;
        else
            $scope.activarListado = true;
    }

    $scope.assignValueAndHide = function (data) {
        $scope.productoBuscar = data.descripcion;
        $scope.activarListado = false;
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