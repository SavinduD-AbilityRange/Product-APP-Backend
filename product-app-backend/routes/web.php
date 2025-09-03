<?php
$router = app('router');
$router->group(['namespace' => 'App\\Http\\Controllers'], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/send-otp', 'AuthController@sendOtp');
    $router->post('/verify-otp', 'AuthController@verifyOtp');
    $router->post('/signup', 'AuthController@signup');
    $router->post('/verify-email', 'AuthController@verifyEmail');
    $router->get('/debug-customer/{id}', 'AuthController@debugCustomer'); 
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/profile', 'AuthController@getProfile');
        $router->put('/profile', 'AuthController@editProfile');
        $router->post('/profile', 'AuthController@editProfile');
        $router->post('/logout', 'AuthController@logout');
        $router->get('/debug-profile-update', 'AuthController@debugProfileUpdate');
        $router->put('/debug-profile-update', 'AuthController@debugProfileUpdate');
    });
});

$router->get('/', function () {
    return response()->json([
        'message' => 'Welcome to Product API',
        'version' => '1.0.0',
        'authentication_flow' => [
            '1. Signup' => 'POST /signup (form-data with user details)',
            '2. Login' => 'POST /login (form-data: email, password)',
            '3. Get Token' => 'Copy the "token" from login response',
            '4. Use Token' => 'Add header: Authorization: Bearer {token}',
            '5. Access Profile' => 'GET /profile (with Authorization header)'
        ],
        'endpoints' => [
            'POST /signup' => 'Register a new user',
            'POST /verify-email' => 'Verify email with OTP',
            'POST /login' => 'User login (returns token)',
            'POST /logout' => 'User logout (requires auth)',
            'GET /profile' => 'Get user profile (requires auth token)',
            'PUT /profile' => 'Update user profile (requires auth token)',
            'GET /form' => 'View interactive profile form',
            'GET /debug-customer/{id}' => 'Debug customer data',
            'GET /test-profile/{id}' => 'Test profile access (no auth needed)',
            'GET /ping' => 'Test API connection',
            'GET /check-env' => 'Check environment configuration',
            'GET /products' => 'List all products',
            'POST /products' => 'Create a new product',
            'PUT /products/{id}' => 'Update a product',
            'DELETE /products/{id}' => 'Delete a product'
        ],
        'example_auth_usage' => [
            'step_1' => 'POST /login with email & password',
            'step_2' => 'Copy token from response: "token": "eyJ0eXAi..."',
            'step_3' => 'GET /profile with header: Authorization: Bearer eyJ0eXAi...',
            'note' => 'Token expires in 3600 seconds (1 hour)'
        ]
    ]);
});


$router->get('/ping', function () {
    return response()->json(['message' => 'API is working']);
});

$router->get('/check-env', function () {
    return response()->json(['DB_DATABASE' => env('DB_DATABASE')]);
});

$router->get('/form', function () {
    return response(file_get_contents(base_path('resources/views/profile-form.html')))
        ->header('Content-Type', 'text/html');
});

$router->get('/test-profile/{id}', function ($id) {
    $customer = \App\Models\Customer::find($id);
    if (!$customer) {
        return response()->json(['error' => 'Customer not found'], 404);
    }
    
    return response()->json([
        'message' => 'Test profile access (no auth required)',
        'customer' => [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'middle_name' => $customer->middle_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'children' => $customer->children,
            'is_verified' => $customer->is_verified
        ]
    ]);
});

$router->post('/test-otp', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'message' => 'OTP Test - Check what you sent',
        'received_data' => $request->all(),
        'otp_code_analysis' => [
            'value' => $request->otp_code ?? 'not_provided',
            'type' => gettype($request->otp_code),
            'length' => $request->otp_code ? strlen((string)$request->otp_code) : 0,
            'is_numeric' => $request->otp_code ? is_numeric($request->otp_code) : false,
            'validation_result' => \Illuminate\Support\Facades\Validator::make($request->all(), [
                'otp_code' => 'required|digits:4'
            ])->passes() ? 'VALID' : 'INVALID'
        ]
    ]);
});

$router->get('/test-auth', ['middleware' => 'auth', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    return response()->json([
        'message' => 'JWT Authentication working!',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ]
    ]);
}]);


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