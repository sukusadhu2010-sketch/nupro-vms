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
use App\Support\Amount;
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
        $products = Product::with('productCategory')->where('status', 'active')->get();
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
            'products.*.unit_price' => 'nullable|numeric|min:0',
            'products.*.total_price' => 'nullable|numeric|min:0',
            'products.*.payment_methods' => 'nullable|array',
            'products.*.payment_methods.*' => 'in:LC,Credit,Advance,PIC,PDC,Proforma Invoice',
            'products.*.moc' => 'required|string|max:255',
            'products.*.mfg_spec' => 'required|string|max:255',
            'products.*.trim' => 'required|string|max:255',
            'products.*.operation' => 'required|string|max:255',
            'products.*.end_connection' => 'required|string|max:255',
            'products.*.rating' => 'required|string|max:255',
            'products.*.media' => 'required|string|max:255',
            'products.*.remarks' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:2000',
            'tax_type' => 'nullable|in:igst,sgst_cgst',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        DB::transaction(function () use ($request) {
            $activeFy = \App\Models\FinancialYear::active();
            if (!$activeFy) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'customer_id' => 'No active financial year. Please activate one in Financial Year Settings before creating an enquiry.',
                ]);
            }

            $enquiryNumber = \App\Services\DocumentNumbering::nextNumber('ENQ', $activeFy);

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
                'enquiry_number' => $enquiryNumber,
                'financial_year_id' => $activeFy->id,
                'priority' => $request->priority,
                'message' => $request->message,
                'attachments' => $attachments,
                'status' => 'pending'
            ]);

            $taxable = 0;
            foreach ($request->products as $item) {
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $taxable += $item['quantity'] * $unitPrice;
                EnquiryItem::create($this->itemData($enquiry->id, $item));
            }

            // Total amount = quantity amount; taxes applied on top
            $taxes = Amount::taxes($taxable, $request->input('tax_type', 'igst'));
            $enquiry->update([
                'total_amount' => $taxable,
                'tax_type' => $taxes['tax_type'],
                'igst' => $taxes['igst'],
                'sgst' => $taxes['sgst'],
                'cgst' => $taxes['cgst'],
                'tax_amount' => $taxes['tax_amount'],
                'amount_in_words' => Amount::inWords($taxes['grand_total']),
            ]);

            // Recalculate total
            $enquiry->refresh();
            $enquiry->save();
        });

        return redirect()->route('enquiries.index')->with('success', 'Enquiry created successfully!');
    }

    public function edit(Enquiry $enquiry)
    {
        // Only pending enquiries can be edited — quoted/closed are locked
        if ($enquiry->status !== 'pending') {
            return redirect()->route('enquiries.show', $enquiry)
                ->with('error', 'This enquiry has been quoted and can no longer be edited.');
        }

        $enquiry->load(['items.product.vendor', 'customer']);

        $productsForForm = $enquiry->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'estimated_price' => $item->estimated_price,
                'notes' => $item->notes,
                'payment_methods' => $item->payment_methods ?? [],
                'moc' => $item->moc,
                'mfg_spec' => $item->mfg_spec,
                'trim' => $item->trim,
                'operation' => $item->operation,
                'end_connection' => $item->end_connection,
                'rating' => $item->rating,
                'media' => $item->media,
                'remarks' => $item->remarks,
            ];
        })->toArray();

        $enquiry->products = $productsForForm;

        $customers = Customer::with('user')->where('status', 'active')->get();
        $products = Product::with('productCategory')->where('status', 'active')->get();
        return view('admin.enquiries.edit', compact('enquiry', 'customers', 'products'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        if ($enquiry->status !== 'pending') {
            return redirect()->route('enquiries.show', $enquiry)
                ->with('error', 'This enquiry has been quoted and can no longer be edited.');
        }

        //return $request->all();
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'priority' => 'required|in:low,medium,high',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'nullable|numeric|min:0',
            'products.*.total_price' => 'nullable|numeric|min:0',
            'products.*.payment_methods' => 'nullable|array',
            'products.*.payment_methods.*' => 'in:LC,Credit,Advance,PIC,PDC,Proforma Invoice',
            'products.*.moc' => 'required|string|max:255',
            'products.*.mfg_spec' => 'required|string|max:255',
            'products.*.trim' => 'required|string|max:255',
            'products.*.operation' => 'required|string|max:255',
            'products.*.end_connection' => 'required|string|max:255',
            'products.*.rating' => 'required|string|max:255',
            'products.*.media' => 'required|string|max:255',
            'products.*.remarks' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,quoted,closed',
            'tax_type' => 'nullable|in:igst,sgst_cgst',
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
            $taxable = 0;
            foreach ($request->products as $item) {
                $taxable += $item['quantity'] * (float) ($item['unit_price'] ?? 0);
                EnquiryItem::create($this->itemData($enquiry->id, $item));
            }

            // Total amount = quantity amount; taxes applied on top
            $taxes = Amount::taxes($taxable, $request->input('tax_type', 'igst'));
            $enquiry->update([
                'total_amount' => $taxable,
                'tax_type' => $taxes['tax_type'],
                'igst' => $taxes['igst'],
                'sgst' => $taxes['sgst'],
                'cgst' => $taxes['cgst'],
                'tax_amount' => $taxes['tax_amount'],
                'amount_in_words' => Amount::inWords($taxes['grand_total']),
            ]);

            // Recalculate total
            $enquiry->refresh();
            $enquiry->save();
        });

        return redirect()->route('enquiries.index')->with('success', 'Enquiry updated successfully!');
    }

    private function itemData(int $enquiryId, array $item): array
    {
        $unitPrice = $item['unit_price'] ?? 0;

        return [
            'enquiry_id' => $enquiryId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $item['quantity'] * $unitPrice,
            'notes' => $item['remarks'] ?? '',
            'payment_methods' => $item['payment_methods'] ?? [],
            'moc' => $item['moc'] ?? null,
            'mfg_spec' => $item['mfg_spec'] ?? null,
            'trim' => $item['trim'] ?? null,
            'operation' => $item['operation'] ?? null,
            'end_connection' => $item['end_connection'] ?? null,
            'rating' => $item['rating'] ?? null,
            'media' => $item['media'] ?? null,
            'remarks' => $item['remarks'] ?? null,
        ];
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

