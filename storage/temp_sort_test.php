<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$q = App\Models\Product::with('productCategory')
    ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
    ->orderBy('product_categories.name')
    ->orderBy('products.name')
    ->select('products.*')
    ->limit(5)
    ->get();

foreach ($q as $p) {
    echo ($p->productCategory->name ?? 'NO CAT') . ' | ' . $p->name . PHP_EOL;
}

$t = App\Models\Product::onlyTrashed()
    ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
    ->orderBy('product_categories.name')
    ->orderBy('products.name')
    ->select('products.*')
    ->limit(5)
    ->get();

echo 'trashed rows: ' . $t->count() . PHP_EOL;
