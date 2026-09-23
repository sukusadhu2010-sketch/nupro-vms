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
        $query = Product::with('productCategory')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('sku', 'like', '%'.$request->search.'%')
                  ->orWhere('hsn_code', 'like', '%'.$request->search.'%')
                  ->orWhereHas('productCategory', function ($cq) use ($request) {
                      $cq->where('name', 'like', '%'.$request->search.'%');
                  });
            });
        }

        if ($request->filled('status')) {

        if($request->status=='out_of_stock'){
            
            $query->where('stock_quantity', '<=', 0);
        } else {
            $query->where('status', $request->status);
        }
        }

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
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
        while (Product::where('sku', $code)->exists()) {
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
     * Delete product.
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }
}

