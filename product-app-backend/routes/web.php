<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () {
    return "Product App API is running";
});

$router->group(['prefix' => 'products'], function () use ($router) {
    $router->get('/', 'ProductController@index');         // GET all products
    $router->post('/', 'ProductController@store');        // POST new product
    $router->get('{id}', 'ProductController@show');       // GET one product
    $router->put('{id}', 'ProductController@update');     // PUT update product
    $router->delete('{id}', 'ProductController@destroy'); // DELETE product
});
