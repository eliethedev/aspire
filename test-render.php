<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
$_SERVER['REQUEST_URI'] = '/supervisor/observations/create';
$_SERVER['SERVER_NAME'] = '127.0.0.1';

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

// Render the response
$response->send();
$kernel->terminate(request(), $response);
