<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'null-category products: ' . App\Models\Product::whereNull('product_category_id')->count() . PHP_EOL;
echo 'total products: ' . App\Models\Product::count() . PHP_EOL;

// Simulate the index listing with a LEFT join (nulls must not be hidden)
$list = App\Models\Product::with('productCategory')
    ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
    ->orderBy('product_categories.name')
    ->orderBy('products.name')
    ->select('products.*')
    ->limit(5)
    ->get();

foreach ($list as $p) {
    echo ($p->productCategory->name ?? 'NO CAT') . ' | ' . $p->name . PHP_EOL;
}
