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

Route::get('/', function () {
    return "Product App API is running";
});
// 1st September 2025 integrated Customer API - Ashini 19:40
Route::post('/customers', 'CustomerController@store'); 

Route::post('/products', 'App\Http\Controllers\SimpleProductController@store');         
Route::group(['prefix' => 'products'], function () {
    Route::get('/', 'ProductController@index');         // GET all products
    Route::post('/', 'ProductController@store');        // POST new product
    Route::get('{id}', 'ProductController@show');       // GET one product
    Route::put('{id}', 'ProductController@update');     // PUT update product
    Route::delete('{id}', 'ProductController@destroy'); // DELETE product
});
// 1st September 2025 integrated APIs  - Ashini 19:44

Route::put('/products/{id}', 'App\Http\Controllers\SimpleProductController@update');     
Route::post('/signup', 'AuthController@signup');
Route::post('/verify-email', 'AuthController@verifyEmail');
Route::post('/login', 'AuthController@login');

// 2nd September 2025 User Interest Routes - Ashini
Route::get('/interests', 'UserInterestController@getInterests');
Route::get('/user-interests', 'UserInterestController@getUserInterests');
Route::post('/user-interests', 'UserInterestController@addUserInterests');
Route::put('/user-interests', 'UserInterestController@updateUserInterests');
Route::delete('/user-interests', 'UserInterestController@deleteUserInterest');
Route::delete('/user-interests/all', 'UserInterestController@deleteAllUserInterests');

