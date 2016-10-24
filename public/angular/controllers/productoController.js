app.controller('productoController', function($scope, $http) {

    $scope.tasa_igv = 0.18;

    $scope.store = function () {
    	$http.post('../productos',
            {   'descripcion':$scope.descripcion,
                'valor_unitario':$scope.val_unit,
                'codigo':$scope.codigo,
                'tasa_igv':$scope.tasa_igv,
                'tasa_isc':$scope.tasa_isc,
                'cod_tipo_sistema_isc':$scope.cod_tipo_sistema_isc
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
                        window.location.href = '#/Productos';
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
        $scope.tasa_isc = "";
    }

    $scope.get = function () {
        $http.get('productos').then(function successCallback(response) {
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
        $scope.tasa_igv = parseFloat(data.tasa_igv);
        $scope.tasa_isc =  parseFloat(data.tasa_isc);
        $scope.cod_tipo_sistema_isc =  data.cod_tipo_sistema_isc;
    }

    $scope.update = function () {
        $http.put('productos/' + $scope.id,
            {   'descripcion':$scope.descripcion,
                'valor_unitario':$scope.valor_unitario,
                'codigo':$scope.codigo,
                'tasa_igv':$scope.tasa_igv,
                'tasa_isc':$scope.tasa_isc,
                'cod_tipo_sistema_isc':$scope.cod_tipo_sistema_isc
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