app.controller('homeController', function($scope, $http, tipoDocumento) {
    $scope.information = {};
    $scope.documentoTipos = tipoDocumento;
    $scope.information.tipo_doc = $scope.documentoTipos[3];

    $scope.getInformation = function () {
        $http.get('information').then(function successCallback(response) {
                if (response.data.length < 1) {
                    $scope.divConfiguration = true;
                }
                else{
                    $scope.divConfiguration = false;
                    $scope.information = response.data;
                }
                $scope.data = response.data;
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    $scope.getDepartamentos = function () {
        $http.get('departamentos').then(function successCallback(response) {
                $scope.departamentos = response.data;
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }

    $scope.selectDepartamento = function () {
        $scope.provincias = $scope.information.departamento.provincias
    }

    $scope.selectProvincia = function () {
        $scope.distritos = $scope.information.provincia.distritos
    }

    $scope.store = function () {

        $scope.information.agente_percep = ($scope.information.agente_percep == undefined) ? false : $scope.information.agente_percep;
        $scope.information.agente_retencion = ($scope.information.agente_retencion == undefined) ? false : $scope.information.agente_percep;

        $scope.information.tipo_doc = $scope.information.tipo_doc.codigo;
        $scope.information.departamento = $scope.information.departamento.departamento;
        $scope.information.provincia = $scope.information.provincia.provincia;
        $scope.information.distrito = $scope.information.distrito.distrito;
        $scope.information.igv = 0.18;
        $scope.information.cod_pais = "PE";
        
        $http.post('../information', $scope.information
            ).then(function successCallback(response) {

                swal({  title: "Perfecto!",
                        text: "Se ha guardado su información",
                        type: "success",   
                        confirmButtonText: "Aceptar",   
                        closeOnConfirm: true
                    });
            }, function errorCallback(response) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            });
    }
});