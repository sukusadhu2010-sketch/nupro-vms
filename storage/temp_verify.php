<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

// 1. Index sort: category groups alphabetical, uncategorized products still included
$all = Product::with('productCategory')
    ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
    ->orderBy('product_categories.name')
    ->orderBy('products.name')
    ->select('products.*')
    ->get();

echo 'index rows: ' . $all->count() . ' (expect 1092)' . PHP_EOL;
echo 'first row: ' . ($all->first()->productCategory->name ?? 'NO CAT') . ' | ' . $all->first()->name . PHP_EOL;

// 2. Soft-delete flow: delete -> appears in trash query -> restore works
$p = Product::create([
    'name' => '__SOFTDEL_TEST__',
    'sku' => 'TEST-SOFTDEL-001',
    'product_category_id' => ProductCategoryHasActive()->id ?? null,
    'status' => 'active',
]);

function ProductCategoryHasActive()
{
    return App\Models\ProductCategory::where('status', 'active')->first();
}

$p->delete();
$inTrash = Product::onlyTrashed()->where('sku', 'TEST-SOFTDEL-001')->exists();
echo 'in trash after delete: ' . ($inTrash ? 'YES' : 'NO') . PHP_EOL;

$p->restore();
$back = Product::where('sku', 'TEST-SOFTDEL-001')->exists();
echo 'restored: ' . ($back ? 'YES' : 'NO') . PHP_EOL;

// clean up test product (permanent, it was only for this test)
$p->forceDelete();
echo 'test product cleaned up' . PHP_EOL;
