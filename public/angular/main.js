
var app = angular.module('App', [
  'ngRoute'
]);

app.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {

    $routeProvider.when("/", {templateUrl: "view/inicio.html"});
    $routeProvider.when("/Productos/nuevo", {templateUrl: "../view/producto/create.html"});

    $routeProvider.otherwise("/404", {templateUrl: "partials/404.html"});

    $locationProvider.html5Mode({ enabled: true, requireBase: false });
}]);