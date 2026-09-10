<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File as F;

$base = __DIR__;

// 1) All view names (dotted)
$viewNames = [];
foreach (F::allFiles($base . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views') as $f) {
    $rel = substr($f->getPathname(), strlen($base) + strlen(DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views') + 1);
    $rel = preg_replace('/\.blade\.php$/', '', $rel);
    $rel = str_replace(['/', '\\'], '.', $rel);
    $viewNames[] = $rel;
}
$viewNames = array_values(array_unique($viewNames));
sort($viewNames);
echo 'TOTAL_VIEWS=' . count($viewNames) . PHP_EOL;

// 2) Combined non-view code
$codeAll = '';
foreach (['app', 'routes', 'config', 'tests', 'database', 'public'] as $d) {
    $dir = $base . DIRECTORY_SEPARATOR . $d;
    if (!is_dir($dir)) {
        continue;
    }
    foreach (F::allFiles($dir) as $f) {
        $ext = $f->getExtension();
        if (in_array($ext, ['php', 'js', 'jsx', 'ts', 'vue'], true)) {
            $codeAll .= "\n" . $f->getContents();
        }
    }
}

// 3) Blade files indexed by dotted name (content per file)
$bladeFiles = [];
$bladeAll = '';
foreach (F::allFiles($base . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views') as $f) {
    $rel = substr($f->getPathname(), strlen($base) + strlen(DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views') + 1);
    $rel = preg_replace('/\.blade\.php$/', '', $rel);
    $rel = str_replace(['/', '\\'], '.', $rel);
    $bladeFiles[$rel] = $f->getContents();
    $bladeAll .= "\n" . $f->getContents();
}

// 4) Find orphans: views not referenced in code and not referenced from ANY other blade
$orphan = [];
foreach ($viewNames as $v) {
    $inCode = substr_count($codeAll, $v苑);
    $inBlade = 0;
    foreach ($bladeFiles as $name => $content) {
        if ($name === $v) continue; // self-reference doesn't count
        if (strpos($content, $v) !== false) {
            $inBlade++;
            break;
        }
    }
    if ($inCode === 0 && $inBlade === 0) {
        $orphan[] = $v;
    }
}

echo 'ORPHAN_COUNT=' . count($orphan) . PHP_EOL;
foreach ($orphan as $o) {
    echo 'ORPHAN ' . $o . PHP_EOL;
}
echo '---DONE---' . PHP_EOL;
