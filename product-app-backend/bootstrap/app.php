<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

// Enable facades and Eloquent ORM
$app->withFacades();
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| These allow you to use config() helper and access files like config/filesystems.php
*/
$app->configure('app');
$app->configure('filesystems'); // ✅ this line is important for image upload

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
*/
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Register global middleware like CORS or others
*/
$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Route Middleware
|--------------------------------------------------------------------------
|
| Assign route-specific middleware
*/
// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);


/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Add any service providers you need (optional)
*/
// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class); // ✅ Needed for image upload

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/../routes/web.php';

return $app;
