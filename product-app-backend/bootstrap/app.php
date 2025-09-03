<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

$app->configure('app');
$app->configure('filesystems'); 
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
]);
$app->routeMiddleware([
    'auth' => App\Http\Middleware\JwtMiddleware::class,
]);

$app->register(App\Providers\AuthServiceProvider::class);

$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
require __DIR__.'/../routes/web.php';

return $app;
