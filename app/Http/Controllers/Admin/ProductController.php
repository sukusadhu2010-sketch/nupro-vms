<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        // Default sort: group products by category (alphabetical), then by product name.
        // LEFT join so products without a category still appear (grouped first).
        $query = Product::with('productCategory')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->orderBy('product_categories.name')
            ->orderBy('products.name')
            ->select('products.*');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('products.name', 'like', '%'.$request->search.'%')
                  ->orWhere('products.sku', 'like', '%'.$request->search.'%')
                  ->orWhere('products.hsn_code', 'like', '%'.$request->search.'%')
                  ->orWhereHas('productCategory', function ($cq) use ($request) {
                      $cq->where('name', 'like', '%'.$request->search.'%');
                  });
            });
        }

        if ($request->filled('status')) {

        if($request->status=='out_of_stock'){

            $query->where('products.stock_quantity', '<=', 0);
        } else {
            $query->where('products.status', $request->status);
        }
        }

        if ($request->filled('category_id')) {
            $query->where('products.product_category_id', $request->category_id);
        }

        $products = $query->paginate(15);

        $categories = ProductCategory::orderBy('name')->pluck('name', 'id');

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Auto-generate the Product Code from Category + Product Name.
     * Format: CATEGORY-NAME (uppercased, slugified), with a numeric
     * suffix appended only if the code already exists (sku is unique in DB).
     */
    private function generateProductCode(?string $category, string $name): string
    {
        $slug = function ($value) {
            return strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $value ?? ''));
        };

        $code = $slug($category) . '-' . $slug($name);
        $code = substr($code, 0, 50) ?: 'PROD';

        $base = $code;
        $i = 1;
        // sku has a DB-level unique index — check across soft-deleted rows too
        while (Product::withTrashed()->where('sku', $code)->exists()) {
            $code = substr($base, 0, 47) . '-' . ++$i;
        }

        return $code;
    }

    /**
     * Show product create form.
     */
    public function create()
    {
        $categories = ProductCategory::where('status', 'active')->orderBy('name')->pluck('name', 'id');
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hsn_code' => 'nullable|string|max:50',
            'product_category_id' => 'required|exists:product_categories,id',
        ]);

        $category = ProductCategory::find($request->product_category_id);

        $data = $request->only(['name', 'hsn_code', 'product_category_id']);
        // Auto-generated Product Code (Category + Product Name)
        $data['sku'] = $this->generateProductCode($category->name ?? null, $request->name);
        // Defaults for fields kept in DB but hidden from the form
        $data['status'] = 'active';

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display product details.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show product edit form.
     */
    public function edit(Product $product)
    {
        $categories = ProductCategory::where('status', 'active')->orderBy('name')->pluck('name', 'id');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hsn_code' => 'nullable|string|max:50',
            'product_category_id' => 'required|exists:product_categories,id',
        ]);

        $category = ProductCategory::find($request->product_category_id);

        $data = $request->only(['name', 'hsn_code', 'product_category_id']);
        // Regenerate the auto Product Code (Category + Product Name)
        $data['sku'] = $this->generateProductCode($category->name ?? null, $request->name);

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Toggle product status (AJAX).
     */
    public function toggleStatus(Request $request, Product $product)
    {
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);

        $badgeClass = $newStatus === 'active' ? 'bg-success' : 'bg-danger';
        $badgeIcon = $newStatus === 'active' ? 'fa-check-circle' : 'fa-ban';
        $badgeText = ucfirst($newStatus);

        $statusBadge = '<span class="badge ' . $badgeClass . ' px-3 py-2">
            <i class="fas ' . $badgeIcon . ' me-1"></i>' . $badgeText . '
        </span>';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status_badge' => $statusBadge,
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product status updated!');
    }

    /**
     * Display the trashed (soft-deleted) products.
     */
    public function trash()
    {
        $products = Product::onlyTrashed()
            ->with('productCategory')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->orderBy('product_categories.name')
            ->orderBy('products.name')
            ->select('products.*')
            ->paginate(15);

        return view('admin.products.trash', compact('products'));
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(Product $product)
    {
        $product->restore();

        return redirect()->route('products.trash')
            ->with('success', 'Product restored successfully!');
    }

    /**
     * Permanently delete a product (only from trash).
     */
    public function forceDelete(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->forceDelete();

        return redirect()->route('products.trash')
            ->with('success', 'Product permanently deleted!');
    }

    /**
     * Delete product (soft delete).
     */
    public function destroy(Product $product)
    {
        $product->delete();

        // Send the user straight to the trash page so the deleted
        // product is visible there — not back to page 1 of the index.
        return redirect()->route('products.trash')
            ->with('success', 'Product deleted successfully! It is shown in the trash below.');
    }
}

