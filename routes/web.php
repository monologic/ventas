<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::resource('productos', 'ProductoController');

Route::resource('identidadDocumentos', 'IdentidadDocumentoController');
Route::get('getDocumento/{numero}', 'IdentidadDocumentoController@getDocumento');

Route::resource('information', 'InformationController');