app.controller('homeController', function($scope, $http) {

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

});