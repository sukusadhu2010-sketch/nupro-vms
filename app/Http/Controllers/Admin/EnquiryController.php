<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Enquiry::with(['customer.user', 'items.product.vendor'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('company', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate(15);
        return view('enquiries.index', compact('enquiries'));
    }

    public function create()
    {
        $customers = Customer::with('user')->where('status', 'active')->get();
        $products = Product::with('vendor')->where('status', 'active')->get();
        return view('admin.enquiries.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'priority' => 'required|in:low,medium,high',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.estimated_price' => 'nullable|numeric|min:0',
            'products.*.notes' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        DB::transaction(function () use ($request) {
            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('enquiries', 'public');
                    $attachments[] = $path;
                }
            }

            $enquiry = Enquiry::create([
                'customer_id' => $request->customer_id,
                'priority' => $request->priority,
                'message' => $request->message,
                'attachments' => $attachments,
                'status' => 'pending'
            ]);

            foreach ($request->products as $item) {
                EnquiryItem::create([
                    'enquiry_id' => $enquiry->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'],
                    'notes' => $item['notes'] ?? ''
                ]);
            }

            // Recalculate total
            $enquiry->refresh();
            $enquiry->save();
        });

        return redirect()->route('enquiries.index')->with('success', 'Enquiry created successfully!');
    }

    public function edit(Enquiry $enquiry)
    {
        $enquiry->load(['items.product.vendor', 'customer']);
        
        $productsForForm = $enquiry->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'estimated_price' => $item->estimated_price,
                'notes' => $item->notes
            ];
        })->toArray();
        
        $enquiry->products = $productsForForm;
        
        $customers = Customer::with('user')->where('status', 'active')->get();
        $products = Product::with('vendor')->where('status', 'active')->get();
        return view('admin.enquiries.edit', compact('enquiry', 'customers', 'products'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        //return $request->all();
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'priority' => 'required|in:low,medium,high',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.estimated_price' => 'nullable|numeric|min:0',
            'products.*.notes' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,quoted,closed',
        ]);

        DB::transaction(function () use ($request, $enquiry) {
            // Handle attachments if needed (skip for simplicity, add if required)
            $attachments = $enquiry->attachments ?? [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('enquiries', 'public');
                    $attachments[] = $path;
                }
            }

            $enquiry->update([
                'customer_id' => $request->customer_id,
                'priority' => $request->priority,
                'message' => $request->message,
                'attachments' => $attachments,
                'status' => $request->status,
            ]);

            // Delete old items
            $enquiry->items()->delete();

            // Create new items
            foreach ($request->products as $item) {
                EnquiryItem::create([
                    'enquiry_id' => $enquiry->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'],
                    'notes' => $item['notes'] ?? ''
                ]);
            }

            // Recalculate total
            $enquiry->refresh();
            $enquiry->save();
        });

        return redirect()->route('enquiries.index')->with('success', 'Enquiry updated successfully!');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();
        return redirect()->route('enquiries.index')->with('success', 'Enquiry deleted!');
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['customer.user', 'items.product.vendor', 'quotations.items.product']);
        return view('admin.enquiries.show', compact('enquiry'));
    }

    // Update status

    public function updateStatus(Request $request, Enquiry $enquiry)
    {
        $request->validate([
            'status' => 'required|in:pending,quoted,closed',
        ]);

        $enquiry->update(['status' => $request->status]);
        return redirect()->route('enquiries.index')->with('success', 'Enquiry status updated!');
    }
   
}

