<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

try {
    $finder = new Illuminate\View\FileViewFinder($app['files'], [__DIR__ . '/resources/views']);
    $compiler = new Illuminate\View\Compilers\BladeCompiler($app['files'], __DIR__ . '/storage/framework/views');

    $path = $finder->find('supervisor.observations.create');
    echo 'View found: ' . $path . PHP_EOL;

    $compiled = $compiler->compile($path);
    echo 'View compiled successfully' . PHP_EOL;

    // Check the compiled file was created
    $compiledPath = $compiler->getCompiledPath($path);
    if (file_exists($compiledPath)) {
        echo 'Compiled file: ' . $compiledPath . PHP_EOL;
        $content = file_get_contents($compiledPath);
        echo 'Size: ' . strlen($content) . ' bytes' . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'In: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
