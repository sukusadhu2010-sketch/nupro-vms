<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;

echo 'Total permissions: ' . Permission::count() . PHP_EOL . PHP_EOL;

try {
    auth()->loginUsingId(1);
    $grouped = Permission::orderBy('module')->orderBy('action')->with('roles')->get()->groupBy('module');
    $html = view('admin.permissions.index', ['grouped' => $grouped])->render();

    $checks = [
        'view-users'   => str_contains($html, 'view-users'),
        'module card'  => str_contains($html, 'customers'),
        'descriptions' => str_contains($html, 'View customers'),
        'role badges'  => str_contains($html, 'Not assigned') || str_contains($html, 'bg-info'),
        'key column'   => str_contains($html, 'Key / Slug'),
    ];
    foreach ($checks as $label => $ok) {
        echo str_pad($label, 15) . ': ' . ($ok ? 'OK' : 'MISSING') . PHP_EOL;
    }
    echo 'Page rendered, length: ' . strlen($html) . PHP_EOL;
} catch (Throwable $e) {
    echo 'RENDER ERR: ' . $e->getMessage() . PHP_EOL;
}
foreach (Permission::orderBy('module')->orderBy('action')->get() as $p) {
    echo $p->id . ' | module=' . ($p->module ?? 'NULL')
        . ' | action=' . ($p->action ?? 'NULL')
        . ' | name=' . ($p->name ?? 'NULL')
        . ' | description=' . ($p->description ?? 'NULL') . PHP_EOL;
}
