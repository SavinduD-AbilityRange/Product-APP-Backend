<?php

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

//[01/09/2025 |Asmitha T| 15.26] - Auth Routes
$router->post('signup', 'AuthController@signup');
$router->post('verify-email', 'AuthController@verifyEmail');

$router->post('login', 'LoginController@login');
$router->post('/request-otp', 'LoginController@requestOtp');
