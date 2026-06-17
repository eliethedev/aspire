<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

try {
    $user = App\Models\User::where('role', 'supervisor')->first();
    if (!$user) {
        echo 'No supervisor user found.' . PHP_EOL;
        exit(1);
    }
    Auth::login($user);
    echo 'Logged in as: ' . $user->name . ' (ID: ' . $user->id . ')' . PHP_EOL;

    $controller = $app->make('App\Http\Controllers\SupervisorController');
    $request = Illuminate\Http\Request::create('/supervisor/observations/create', 'GET');
    $result = $controller->createObservation();
    echo 'Result type: ' . get_class($result) . PHP_EOL;

    $viewData = $result->getData();
    echo 'Teacher count: ' . count($viewData['teacherData'] ?? []) . PHP_EOL;
    echo 'School head count: ' . count($viewData['schoolHeadData'] ?? []) . PHP_EOL;

    $html = $result->render();
    echo 'HTML length: ' . strlen($html) . ' bytes' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}
