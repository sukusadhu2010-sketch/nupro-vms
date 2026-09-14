<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\QuotationForm;
use Illuminate\Http\Request;

class QuotationFormController extends Controller
{
    public function index()
    {
        $forms = QuotationForm::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.quotation-forms.index', compact('forms'));
    }

    public function create(Request $request)
    {
        $enquiry = $request->filled('enquiry_id') ? Enquiry::with('customer')->find($request->enquiry_id) : null;

        $form = new QuotationForm([
            'company_name' => 'Engineering Syndicate (P) Ltd.',
            'certification' => 'An ISO 9001:2015 Certified Company',
            'title' => 'QUOTATION / OFFER',
            'form_date' => now()->toDateString(),
            'acknowledgement' => 'With reference to your enquiry, we thank you for the opportunity and are pleased to submit our quotation for your kind consideration as under:',
            'delivery_terms' => 'F.O.R. site',
            'warranty_terms' => '18 months from the date of supply or 12 months from the date of commissioning, whichever is earlier',
            'closing_statement' => 'Thanking You',
            'payment_terms' => '30% advance along with purchase order, 60% against proforma invoice before dispatch, 10% after commissioning / within 30 days of dispatch, whichever is earlier.',
            'inspection_vendor_scope' => 'Vendor\'s routine inspection and test certificates will be provided.',
            'inspection_third_party_scope' => 'Third party inspection, if required, shall be arranged at buyer\'s cost prior to dispatch.',
            'enquiry_id' => $enquiry?->id,
        ]);

        // Pre-fill from enquiry when available
        if ($enquiry && $enquiry->customer) {
            $form->to_company = $enquiry->customer->name;
            $form->contact_person = $enquiry->customer->user->name ?? null;
        }

        $form->ref_no = QuotationForm::generateRefNo($form->form_date);

        return view('admin.quotation-forms.form', [
            'form' => $form,
            'mode' => 'create',
            'products' => $this->productOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateForm($request);
        $data['grand_total'] = $this->computeGrandTotal($data['items'] ?? []);

        QuotationForm::create($data);

        return redirect()->route('quotation-forms.index')
            ->with('success', 'Quotation form ' . $data['ref_no'] . ' saved successfully!');
    }

    public function edit(QuotationForm $quotationForm)
    {
        return view('admin.quotation-forms.form', [
            'form' => $quotationForm,
            'mode' => 'edit',
            'products' => $this->productOptions(),
        ]);
    }

    /** Product list for the auto-populate dropdown. */
    private function productOptions()
    {
        return Product::orderBy('name')->get(['id', 'name']);
    }

    public function update(Request $request, QuotationForm $quotationForm)
    {
        $data = $this->validateForm($request, $quotationForm->id);
        $data['grand_total'] = $this->computeGrandTotal($data['items'] ?? []);

        $quotationForm->update($data);

        return redirect()->route('quotation-forms.show', $quotationForm)
            ->with('success', 'Quotation form updated successfully!');
    }

    public function show(QuotationForm $quotationForm)
    {
        return view('admin.quotation-forms.show', ['form' => $quotationForm]);
    }

    public function destroy(QuotationForm $quotationForm)
    {
        $quotationForm->delete();
        return redirect()->route('quotation-forms.index')->with('success', 'Quotation form deleted!');
    }

    /** Printable / export view */
    public function print(QuotationForm $quotationForm)
    {
        return view('admin.quotation-forms.print', ['form' => $quotationForm]);
    }

    public function finalize(QuotationForm $quotationForm)
    {
        $quotationForm->update(['status' => 'final']);
        return back()->with('success', 'Quotation form marked as final!');
    }

    private function validateForm(Request $request, $ignoreId = null): array
    {
        $rules = [
            // Header
            'company_name' => 'required|string|max:255',
            'certification' => 'nullable|string|max:255',
            'ref_no' => 'required|string|max:100|unique:quotation_forms,ref_no' . ($ignoreId ? ',' . $ignoreId : ''),
            'form_date' => 'required|date',
            'title' => 'required|string|max:255',

            // Recipient (mandatory)
            'to_company' => 'required|string|max:255',
            'to_address' => 'nullable|string|max:1000',
            'project_details' => 'nullable|string|max:1000',
            'contact_person' => 'required|string|max:255',
            'contact_designation' => 'nullable|string|max:255',

            // Body (mandatory)
            'acknowledgement' => 'nullable|string|max:2000',
            'product_spec' => 'required|string|max:255',

            'description_entries' => 'required|array|min:1',
            'description_entries.*.moc' => 'required|string|max:255',
            'description_entries.*.mfg_spec' => 'required|string|max:255',
            'description_entries.*.trim' => 'required|string|max:255',
            'description_entries.*.operation' => 'required|string|max:255',
            'description_entries.*.end_connection' => 'required|string|max:255',
            'description_entries.*.rating' => 'required|string|max:255',
            'description_entries.*.media' => 'required|string|max:255',

            // Items (mandatory) — repeater rows
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.size_mm' => 'required|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'nullable|numeric|min:0',
            'items.*.payment_modes' => 'nullable|array',
            'items.*.payment_modes.*' => 'in:LC,Credit,Advance,PIC,PDC,Proforma Invoice',

            // Terms (mandatory)
            'delivery_terms' => 'required|string|max:255',
            'igst_percent' => 'nullable|numeric|min:0|max:100',
            'sgst_percent' => 'nullable|numeric|min:0|max:100',
            'cgst_percent' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'required|string|max:2000',
            'inspection_vendor_scope' => 'nullable|string|max:1000',
            'inspection_third_party_scope' => 'nullable|string|max:1000',
            'warranty_terms' => 'required|string|max:500',

            // Footer
            'notes' => 'nullable|string|max:2000',
            'closing_statement' => 'nullable|string|max:255',
            'signatory_company' => 'required|string|max:255',
            'signatory_designation' => 'required|string|max:255',

            'enquiry_id' => 'nullable|exists:enquiries,id',
            'status' => 'nullable|in:draft,final',
        ];

        $data = $request->validate($rules);

        $data['igst_percent'] = $data['igst_percent'] ?? 0;
        $data['sgst_percent'] = $data['sgst_percent'] ?? 0;
        $data['cgst_percent'] = $data['cgst_percent'] ?? 0;
        $data['status'] = $data['status'] ?? 'draft';

        return $data;
    }

    private function computeGrandTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += ((float) $item['quantity']) * ((float) $item['unit_price']);
        }
        return $total;
    }
}
