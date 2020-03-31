<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'orders'], function() use ($router){
    $router->get('/store', 'OrdersController@getDataStore');
    $router->get('/celler', 'OrdersController@getDataCeller');
    $router->get('/toSupply', 'OrdersController@getProductsToSupply');
    $router->post('/', 'OrdersController@getOrder');
    $router->post('/status', 'OrdersController@changeStatus');
    $router->post('/print', 'OrdersController@printTicket');
    $router->post('/pdf', 'OrdersController@getPdf');
    $router->post('/generate', 'OrdersController@generateOrder');

});

$router->group(['prefix' => 'prices'], function() use ($router){
    $router->get('/list', 'PriceController@get');
    $router->post('/', 'PriceController@getProduct');
});