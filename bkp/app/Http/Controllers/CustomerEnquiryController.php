<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Customer;

class CustomerEnquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer']);
    }

    public function index()
    {
        $customer = Auth::user()->customer;
        $enquiries = Enquiry::where('customer_id', $customer->id)->with('customer')->latest()->paginate(10);
        return view('customer.enquiries.index', compact('enquiries'));
    }

    public function products()
    {
        $products = Product::with('vendor')->where('status', 'active')->paginate(24);
        return view('customer.products.index', compact('products'));
    }

    public function create()
    {
        $products = Product::with('vendor')->where('status', 'active')->get();
        return view('customer.enquiries.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx', // 5MB
        ]);

        $customer = Auth::user()->customer;

        // Calculate total
        $total = 0;
        $productData = [];
        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;
            $productData[] = [
                'id' => $item['product_id'],
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'notes' => $item['notes'] ?? '',
                'subtotal' => $subtotal
            ];
        }

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('enquiries/'.time(), 'public');
                $attachments[] = $path;
            }
        }

        Enquiry::create([
            'customer_id' => $customer->id,
            'products' => $productData,
            'total_amount' => $total,
            'message' => $request->message,
            'attachments' => $attachments,
            'status' => 'pending'
        ]);

        return redirect()->route('customer.enquiries.index')
            ->with('success', 'Enquiry submitted successfully!');
    }

    public function show(Enquiry $enquiry)
    {
        if ($enquiry->customer_id != Auth::user()->customer->id) {
            abort(403);
        }
        return view('customer.enquiries.show', compact('enquiry'));
    }
}

