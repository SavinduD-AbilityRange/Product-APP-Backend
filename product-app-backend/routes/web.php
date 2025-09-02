<?php
$router = app('router');
$router->group(['namespace' => 'App\\Http\\Controllers'], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/send-otp', 'AuthController@sendOtp');
    $router->post('/verify-otp', 'AuthController@verifyOtp');
    $router->post('/signup', 'AuthController@signup');
    $router->post('/verify-email', 'AuthController@verifyEmail');
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/profile', 'AuthController@getProfile');
        $router->put('/profile', 'AuthController@editProfile');
        $router->post('/logout', 'AuthController@logout');
    });
});

$router->get('/', function () {
    return response()->json([
        'message' => 'Welcome to Product API',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /signup' => 'Register a new user',
            'POST /verify-email' => 'Verify email with OTP',
            'POST /login' => 'User login',
            'POST /logout' => 'User logout (requires auth)',
            'GET /profile' => 'Get user profile (requires auth)',
            'PUT /profile' => 'Update user profile (requires auth)',
            'GET /ping' => 'Test API connection',
            'GET /check-env' => 'Check environment configuration',
            'GET /products' => 'List all products',
            'POST /products' => 'Create a new product',
            'PUT /products/{id}' => 'Update a product',
            'DELETE /products/{id}' => 'Delete a product'
        ]
    ]);
});


$router->get('/ping', function () {
    return response()->json(['message' => 'API is working']);
});

$router->get('/check-env', function () {
    return response()->json(['DB_DATABASE' => env('DB_DATABASE')]);
});


$router->get('/products', 'App\\Http\\Controllers\\SimpleProductController@index');         
$router->get('/products/{id}', 'App\\Http\\Controllers\\SimpleProductController@show');       
$router->post('/products', 'App\\Http\\Controllers\\SimpleProductController@store');          
$router->put('/products/{id}', 'App\\Http\\Controllers\\SimpleProductController@update');     
$router->patch('/products/{id}', 'App\\Http\\Controllers\\SimpleProductController@update');   
$router->delete('/products/{id}', 'App\\Http\\Controllers\\SimpleProductController@destroy'); 


$router->get('/storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'Image not found');
    }
    
    $type = 'image/jpeg';
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