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

// 🏠 Root route
Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to Product API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /ping' => 'Test API connection',
            'GET /check-env' => 'Check environment configuration',
            'GET /products' => 'List all products',
            'POST /products' => 'Create a new product',
            'PUT /products/{id}' => 'Update a product',
            'DELETE /products/{id}' => 'Delete a product'
        ]
    ]);
});

// 🧪 Test routes
Route::get('/ping', function () {
    return response()->json(['message' => 'API is working ✅']);
});

Route::get('/check-env', function () {
    return response()->json(['DB_DATABASE' => env('DB_DATABASE')]);
});

// 📦 PRODUCT CRUD ROUTES
Route::get('/products', 'App\Http\Controllers\SimpleProductController@index');           // List all products
Route::get('/products/{id}', 'App\Http\Controllers\SimpleProductController@show');       // Get a single product
Route::post('/products', 'App\Http\Controllers\SimpleProductController@store');          // Add a new product
Route::put('/products/{id}', 'App\Http\Controllers\SimpleProductController@update');     // Update an existing product
Route::patch('/products/{id}', 'App\Http\Controllers\SimpleProductController@update');   // Update an existing product (PATCH)
Route::delete('/products/{id}', 'App\Http\Controllers\SimpleProductController@destroy'); // Delete a product

// 🖼️ IMAGE SERVING ROUTE
Route::get('/storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'Image not found');
    }
    
    $type = 'image/jpeg'; // Default to JPEG
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if ($extension === 'png') {
        $type = 'image/png';
    } elseif ($extension === 'gif') {
        $type = 'image/gif';
    }
    
    return response()->file($path, [
        'Content-Type' => $type
    ]);
});
