<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = App\Models\User::where('role','supervisor')->first(); auth()->setUser($u);
$h = app(App\Http\Controllers\Api\SyncController::class)->offlinePage()->render();
preg_match_all('#<script>(.*?)</script>#s', $h, $m);
$ok = true;
foreach ($m[1] as $i => $block) {
    // Skip the tiny base-url stub (contains unresolvable Blade echo artifacts).
    if (strlen(trim($block)) < 200) continue;
    file_put_contents('C:\\Users\\LAPTOP~1\\AppData\\Local\\Temp\\opencode\\rb'.$i.'.js', $block);
    exec('node --check "C:\\Users\\LAPTOP~1\\AppData\\Local\\Temp\\opencode\\rb'.$i.'.js" 2>&1', $o, $code);
    echo 'rendered block '.$i.': '.($code === 0 ? 'JS OK' : 'JS FAIL').PHP_EOL;
    if ($code !== 0) { $ok = false; echo implode(PHP_EOL, array_slice($o, 0, 4)).PHP_EOL; }
}
exit($ok ? 0 : 1);
