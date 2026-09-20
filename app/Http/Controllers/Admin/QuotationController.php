<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Support\Amount;

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

        // Preview next FY-aware quote number (not consumed until save)
        $quoteNumber = \App\Services\DocumentNumbering::peek('QTN') ?? 'NO ACTIVE FY';
        $activeFy = \App\Models\FinancialYear::active();

        return view('admin.quotations.create', compact('enquiry', 'nextVersion', 'quoteNumber', 'activeFy'));
    }

    public function store(Request $request, Enquiry $enquiry)
    {
        $request->validate([
            'version' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'nullable|numeric|min:0',
            'items.*.payment_methods' => 'nullable|array',
            'items.*.payment_methods.*' => 'in:LC,Credit,Advance,PIC,PDC,Proforma Invoice',
            'items.*.moc' => 'required|string|max:255',
            'items.*.mfg_spec' => 'required|string|max:255',
            'items.*.trim' => 'required|string|max:255',
            'items.*.operation' => 'required|string|max:255',
            'items.*.end_connection' => 'required|string|max:255',
            'items.*.rating' => 'required|string|max:255',
            'items.*.media' => 'required|string|max:255',
            'items.*.remarks' => 'nullable|string|max:1000',
            'tax_type' => 'nullable|in:igst,sgst_cgst',
            'notes' => 'nullable|string|max:65000',
            // Acknowledgement & Product Specification
            'acknowledgement' => 'nullable|string|max:2000',
            'customer_information' => 'nullable|string|max:5000',
            'kind_attention' => 'nullable|string|max:255',
            'product_spec' => 'nullable|string|max:255',
            // Terms & Conditions
            'delivery_terms' => 'nullable|string|max:255',
            'warranty_terms' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:2000',
            'prices_basis' => 'nullable|string|max:2000',
            'prices_basis_option' => 'nullable|in:ex-works,transported-godown',
            'payment_term_option' => 'nullable|string|max:50',
            'payment_term_text' => 'nullable|array',
            'payment_term_text.*' => 'nullable|string|max:2000',
            'inspection_vendor_scope' => 'nullable|string|max:2000',
            'inspection_third_party_scope' => 'nullable|string|max:2000',
            // Notes & Signatory
            'closing_statement' => 'nullable|string|max:255',
            'signatory_company' => 'nullable|string|max:255',
            'signatory_designation' => 'nullable|string|max:255',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $quoteNumber = DB::transaction(function () use ($request, $enquiry) {
            $activeFy = \App\Models\FinancialYear::active();
            if (!$activeFy) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quote_number' => 'No active financial year. Please activate one in Financial Year Settings before creating a quotation.',
                ]);
            }

            // Generate FY-aware quote number atomically (read-only for the user)
            $quoteNumber = \App\Services\DocumentNumbering::nextNumber('QTN', $activeFy);
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

            // Total amount = quantity amount; taxes applied on top
            $taxes = Amount::taxes((float) $totalAmount, $request->input('tax_type', 'igst'));

            $quotation = Quotation::create([
                'enquiry_id' => $enquiry->id,
                'financial_year_id' => $activeFy->id,
                'quote_number' => $quoteNumber,
                'version' => $request->version,
                'status' => 'draft',
                'total_amount' => $totalAmount,
                'tax_type' => $taxes['tax_type'],
                'igst' => $taxes['igst'],
                'sgst' => $taxes['sgst'],
                'cgst' => $taxes['cgst'],
                'tax_amount' => $taxes['tax_amount'],
                'amount_in_words' => Amount::inWords($taxes['grand_total']),
                'notes' => \App\Support\HtmlSanitizer::clean($request->notes),
                'attachments' => $attachments,
                'valid_until' => $request->valid_until,
                'acknowledgement' => $request->acknowledgement,
                'customer_information' => $request->customer_information,
                'kind_attention' => $request->kind_attention,
                'product_spec' => $request->product_spec,
                'delivery_terms' => $request->delivery_terms,
                'warranty_terms' => $request->warranty_terms,
                'payment_terms' => $request->payment_terms,
                'prices_basis' => $request->prices_basis,
                'prices_basis_option' => $request->prices_basis_option,
                'payment_term_option' => $request->payment_term_option,
                'payment_term_text' => $request->input('payment_term_text', []),
                'inspection_vendor_scope' => $request->inspection_vendor_scope,
                'inspection_third_party_scope' => $request->inspection_third_party_scope,
                'closing_statement' => $request->closing_statement,
                'signatory_company' => $request->signatory_company,
                'signatory_designation' => $request->signatory_designation,
            ]);

            foreach ($request->items as $item) {
                QuotationItem::create($this->itemData($quotation->id, $item));
            }

            // Update enquiry status to quoted if pending
            if ($enquiry->status === 'pending') {
                $enquiry->update(['status' => 'quoted']);
            }

            return $quoteNumber;
        });

        return redirect()->route('enquiries.show', $enquiry)
            ->with('success', 'Quotation ' . $quoteNumber . ' generated successfully!');
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
            'items.*.total_price' => 'nullable|numeric|min:0',
            'items.*.payment_methods' => 'nullable|array',
            'items.*.payment_methods.*' => 'in:LC,Credit,Advance,PIC,PDC,Proforma Invoice',
            'items.*.moc' => 'required|string|max:255',
            'items.*.mfg_spec' => 'required|string|max:255',
            'items.*.trim' => 'required|string|max:255',
            'items.*.operation' => 'required|string|max:255',
            'items.*.end_connection' => 'required|string|max:255',
            'items.*.rating' => 'required|string|max:255',
            'items.*.media' => 'required|string|max:255',
            'items.*.remarks' => 'nullable|string|max:1000',
            'tax_type' => 'nullable|in:igst,sgst_cgst',
            'notes' => 'nullable|string|max:65000',
            // Acknowledgement & Product Specification
            'acknowledgement' => 'nullable|string|max:2000',
            'customer_information' => 'nullable|string|max:5000',
            'kind_attention' => 'nullable|string|max:255',
            'product_spec' => 'nullable|string|max:255',
            // Terms & Conditions
            'delivery_terms' => 'nullable|string|max:255',
            'warranty_terms' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:2000',
            'prices_basis' => 'nullable|string|max:2000',
            'prices_basis_option' => 'nullable|in:ex-works,transported-godown',
            'payment_term_option' => 'nullable|string|max:50',
            'payment_term_text' => 'nullable|array',
            'payment_term_text.*' => 'nullable|string|max:2000',
            'inspection_vendor_scope' => 'nullable|string|max:2000',
            'inspection_third_party_scope' => 'nullable|string|max:2000',
            // Notes & Signatory
            'closing_statement' => 'nullable|string|max:255',
            'signatory_company' => 'nullable|string|max:255',
            'signatory_designation' => 'nullable|string|max:255',
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

            // Total amount = quantity amount; taxes applied on top
            $taxes = Amount::taxes((float) $totalAmount, $request->input('tax_type', 'igst'));

            $quotation->update([
                'total_amount' => $totalAmount,
                'tax_type' => $taxes['tax_type'],
                'igst' => $taxes['igst'],
                'sgst' => $taxes['sgst'],
                'cgst' => $taxes['cgst'],
                'tax_amount' => $taxes['tax_amount'],
                'amount_in_words' => Amount::inWords($taxes['grand_total']),
                'notes' => \App\Support\HtmlSanitizer::clean($request->notes),
                'attachments' => $attachments,
                'valid_until' => $request->valid_until,
                'acknowledgement' => $request->acknowledgement,
                'customer_information' => $request->customer_information,
                'kind_attention' => $request->kind_attention,
                'product_spec' => $request->product_spec,
                'delivery_terms' => $request->delivery_terms,
                'warranty_terms' => $request->warranty_terms,
                'payment_terms' => $request->payment_terms,
                'prices_basis' => $request->prices_basis,
                'prices_basis_option' => $request->prices_basis_option,
                'payment_term_option' => $request->payment_term_option,
                'payment_term_text' => $request->input('payment_term_text', []),
                'inspection_vendor_scope' => $request->inspection_vendor_scope,
                'inspection_third_party_scope' => $request->inspection_third_party_scope,
                'closing_statement' => $request->closing_statement,
                'signatory_company' => $request->signatory_company,
                'signatory_designation' => $request->signatory_designation,
            ]);

            // Delete old items and recreate
            $quotation->items()->delete();

            foreach ($request->items as $item) {
                QuotationItem::create($this->itemData($quotation->id, $item));
            }
        });

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully!');
    }

    private function itemData(int $quotationId, array $item): array
    {
        return [
            'quotation_id' => $quotationId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['quantity'] * $item['unit_price'],
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

        // Preview next FY-aware quote number (not consumed until save)
        $quoteNumber = \App\Services\DocumentNumbering::peek('QTN') ?? 'NO ACTIVE FY';
        $activeFy = \App\Models\FinancialYear::active();

        // Mark current as revised if not already
        if ($quotation->status !== 'accepted') {
            $quotation->update(['status' => 'revised']);
        }

        return view('admin.quotations.create', compact('enquiry', 'nextVersion', 'quoteNumber', 'activeFy'));
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['enquiry.customer.user', 'items.product.vendor']);
        return view('admin.quotations.print', compact('quotation'));
    }
}

