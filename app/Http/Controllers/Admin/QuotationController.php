<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with(['enquiry.customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('admin.quotations.index', compact('quotations'));
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['enquiry.customer.user', 'items.product.vendor']);
        return view('admin.quotations.show', compact('quotation'));
    }

    public function create(Enquiry $enquiry)
    {
        $enquiry->load(['items.product', 'customer']);
        
        // Determine next version
        $latestVersion = $enquiry->quotations()->max('version') ?? 0;
        $nextVersion = $latestVersion + 1;
        
        // Generate quote number
        $quoteNumber = 'QTN-' . date('Y') . '-' . str_pad($enquiry->id, 3, '0', STR_PAD_LEFT) . '-V' . $nextVersion;
        
        return view('admin.quotations.create', compact('enquiry', 'nextVersion', 'quoteNumber'));
    }

    public function store(Request $request, Enquiry $enquiry)
    {
        $request->validate([
            'quote_number' => 'required|string|unique:quotations,quote_number',
            'version' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        DB::transaction(function () use ($request, $enquiry) {
            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachments[] = $file->store('quotations', 'public');
                }
            }

            // If previous versions exist and are not already revised, mark them as revised
            $enquiry->quotations()
                ->where('status', '!=', 'accepted')
                ->where('status', '!=', 'revised')
                ->update(['status' => 'revised']);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $quotation = Quotation::create([
                'enquiry_id' => $enquiry->id,
                'quote_number' => $request->quote_number,
                'version' => $request->version,
                'status' => 'draft',
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'attachments' => $attachments,
                'valid_until' => $request->valid_until,
            ]);

            foreach ($request->items as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? '',
                ]);
            }

            // Update enquiry status to quoted if pending
            if ($enquiry->status === 'pending') {
                $enquiry->update(['status' => 'quoted']);
            }
        });

        return redirect()->route('enquiries.show', $enquiry)
            ->with('success', 'Quotation ' . $request->quote_number . ' generated successfully!');
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['enquiry.customer', 'items.product']);
        return view('admin.quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        DB::transaction(function () use ($request, $quotation) {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Handle attachments
            $attachments = $quotation->attachments ?? [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachments[] = $file->store('quotations', 'public');
                }
            }

            $quotation->update([
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'attachments' => $attachments,
                'valid_until' => $request->valid_until,
            ]);

            // Delete old items and recreate
            $quotation->items()->delete();

            foreach ($request->items as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? '',
                ]);
            }
        });

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully!');
    }

    public function destroy(Quotation $quotation)
    {
        $enquiry = $quotation->enquiry;
        $quotation->delete();
        return redirect()->route('enquiries.show', $enquiry)
            ->with('success', 'Quotation deleted!');
    }

    public function send(Quotation $quotation)
    {
        if ($quotation->status === 'draft') {
            $quotation->update(['status' => 'sent']);
            return back()->with('success', 'Quotation marked as sent!');
        }
        return back()->with('error', 'Only draft quotations can be sent.');
    }

    public function accept(Request $request, Quotation $quotation)
    {
        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return back()->with('error', 'Only draft or sent quotations can be accepted.');
        }

        DB::transaction(function () use ($quotation, $request) {
            $quotation->update(['status' => 'accepted']);

            // Mark other quotations for this enquiry as expired
            Quotation::where('enquiry_id', $quotation->enquiry_id)
                ->where('id', '!=', $quotation->id)
                ->where('status', '!=', 'accepted')
                ->update(['status' => 'expired']);

            // Optionally close the enquiry
            if ($request->boolean('close_enquiry')) {
                $quotation->enquiry->update(['status' => 'closed']);
            }
        });

        $message = 'Quotation accepted!';
        if ($request->boolean('close_enquiry')) {
            $message .= ' Enquiry has been closed.';
        }

        return redirect()->route('enquiries.show', $quotation->enquiry_id)
            ->with('success', $message);
    }

    public function revise(Quotation $quotation)
    {
        $quotation->load(['enquiry.items.product', 'items.product']);
        $enquiry = $quotation->enquiry;
        
        // Determine next version
        $nextVersion = $enquiry->quotations()->max('version') + 1;
        
        // Generate new quote number based on original
        $quoteNumber = 'QTN-' . date('Y') . '-' . str_pad($enquiry->id, 3, '0', STR_PAD_LEFT) . '-V' . $nextVersion;
        
        // Mark current as revised if not already
        if ($quotation->status !== 'accepted') {
            $quotation->update(['status' => 'revised']);
        }
        
        return view('admin.quotations.create', compact('enquiry', 'nextVersion', 'quoteNumber'));
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['enquiry.customer.user', 'items.product.vendor']);
        return view('admin.quotations.print', compact('quotation'));
    }
}

