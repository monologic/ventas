app.controller('productoController', function($scope, $http) {
    $scope.store = function () {
    	$http.post('admin/banco',
            {   'descripcion':$scope.descripcion,
                'valor_unitario':$scope.valor_unitario,
                'codigo':$scope.codigo
            }).then(function successCallback(response) {
                
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }
});