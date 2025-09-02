<?php

$router->get('/', function () {
    return "Product App API is running";
});

$router->group(['prefix' => 'products'], function () use ($router) {
    $router->get('/', 'ProductController@index');         
    $router->post('/', 'ProductController@store');        
    $router->get('{id}', 'ProductController@show');       
    $router->put('{id}', 'ProductController@update');     
    $router->delete('{id}', 'ProductController@destroy'); 
});

//[01/09/2025 |Asmitha T| 15.26] - Auth Routes
$router->post('signup', 'AuthController@signup');
$router->post('verify-email', 'AuthController@verifyEmail');

$router->post('login', 'LoginController@login');
$router->post('/request-otp', 'LoginController@requestOtp');
//[02/09/2025 |Asmitha T| 11.11] - User Interest Routes
$router->post('user/interests', 'UserInterestController@store');
$router->get('user/interests/{userId}', 'UserInterestController@show');
