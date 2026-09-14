<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories.
     */
    public function index(Request $request)
    {
        $query = ProductCategory::withCount('products')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->paginate(15);

        return view('admin.product-categories.index', compact('categories'));
    }

    /**
     * Show category create form.
     */
    public function create()
    {
        return view('admin.product-categories.create');
    }

    /**
     * Store new category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:product_categories,name|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        ProductCategory::create($request->only(['name', 'description', 'status']));

        return redirect()->route('product-categories.index')
            ->with('success', 'Product category created successfully!');
    }

    /**
     * Display category details with its products.
     */
    public function show(ProductCategory $product_category)
    {
        $product_category->load(['products' => function ($q) {
            $q->latest();
        }]);

        return view('admin.product-categories.show', ['category' => $product_category]);
    }

    /**
     * Show category edit form.
     */
    public function edit(ProductCategory $product_category)
    {
        return view('admin.product-categories.edit', ['category' => $product_category]);
    }

    /**
     * Update category.
     */
    public function update(Request $request, ProductCategory $product_category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'name')->ignore($product_category)],
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $product_category->update($request->only(['name', 'description', 'status']));

        return redirect()->route('product-categories.index')
            ->with('success', 'Product category updated successfully!');
    }

    /**
     * Toggle category status (AJAX).
     */
    public function toggleStatus(Request $request, ProductCategory $product_category)
    {
        $newStatus = $product_category->status === 'active' ? 'inactive' : 'active';
        $product_category->update(['status' => $newStatus]);

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

        return redirect()->route('product-categories.index')
            ->with('success', 'Product category status updated!');
    }

    /**
     * Delete category.
     */
    public function destroy(ProductCategory $product_category)
    {
        try {
            if ($product_category->products()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete: this category is assigned to one or more products.',
                ], 422);
            }

            $product_category->delete();

            return response()->json(['status' => true, 'message' => 'Product category deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to delete category.'], 500);
        }
    }
}
