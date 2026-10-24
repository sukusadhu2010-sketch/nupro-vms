<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OrganizationSetting;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'allocations.invoice'])
            ->where(function ($q) {
                $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received');
            })
            ->latest('payment_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $receipts = $query->paginate(15)->withQueryString();

        $metrics = [
            'total_received' => Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received'))->sum('amount'),
            'this_month' => Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received'))
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'total_outstanding' => Invoice::where('status', '!=', 'cancelled')->get()->sum->outstanding,
        ];

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('admin.receipts.index', compact('receipts', 'metrics', 'customers'));
    }

    public function create(Request $request)
    {
        $receiptNumber = Payment::generatePaymentNumber(Payment::DIRECTION_RECEIPT);
        $customers = Customer::orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');
        $selectedInvoiceId = $request->input('invoice_id');

        return view('admin.receipts.create', compact('receiptNumber', 'customers', 'selectedCustomerId', 'selectedInvoiceId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_number' => 'required|string|unique:payments,payment_number',
            'payment_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,bank_transfer,cheque,neft,rtgs,upi',
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('payments')->where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)),
            ],
            'bank_name' => 'nullable|string|max:100',
            'cheque_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required_with:allocations|exists:invoices,id',
            'allocations.*.allocated_amount' => 'required_with:allocations|numeric|min:0.01',
        ]);

        // Validate allocations total does not exceed total receipt amount
        $allocations = $request->input('allocations', []);
        $totalAllocated = array_sum(array_column($allocations, 'allocated_amount'));
        if ($totalAllocated > (float) $validated['amount']) {
            return back()->withErrors(['amount' => "Total allocated amount (₹{$totalAllocated}) cannot exceed the payment amount (₹{$validated['amount']})."])->withInput();
        }

        $payment = DB::transaction(function () use ($validated, $allocations, $request) {
            $payment = Payment::create([
                'payment_number' => $validated['payment_number'],
                'direction' => Payment::DIRECTION_RECEIPT,
                'customer_id' => $validated['customer_id'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_mode' => $validated['payment_mode'],
                'reference_number' => $validated['reference_number'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'cheque_date' => $validated['cheque_date'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]);

            foreach ($allocations as $alloc) {
                $inv = Invoice::lockForUpdate()->find($alloc['invoice_id']);
                if ($inv) {
                    $allocAmt = min((float) $alloc['allocated_amount'], $inv->outstanding);

                    if ($allocAmt > 0) {
                        PaymentAllocation::create([
                            'payment_id' => $payment->id,
                            'invoice_id' => $inv->id,
                            'allocated_amount' => $allocAmt,
                        ]);

                        $newPaid = (float) $inv->paid_amount + $allocAmt;
                        $status = ($newPaid >= (float) $inv->grand_total) ? 'paid' : 'partially_paid';

                        $inv->update([
                            'paid_amount' => $newPaid,
                            'status' => $status,
                        ]);
                    }
                }
            }

            AuditLog::record('payment_created', 'receipts', "Received ₹{$payment->amount} from {$payment->customer?->name} (Receipt #{$payment->payment_number})");

            return $payment;
        });

        return redirect()->route('receipts.show', $payment)
            ->with('success', "Customer Receipt {$payment->payment_number} recorded successfully.");
    }

    public function show(Payment $receipt)
    {
        $receipt->load(['customer', 'allocations.invoice', 'createdBy']);
        $org = OrganizationSetting::current();

        return view('admin.receipts.show', compact('receipt', 'org'));
    }

    public function destroy(Payment $receipt)
    {
        DB::transaction(function () use ($receipt) {
            // Rollback invoice allocations
            foreach ($receipt->allocations as $alloc) {
                $inv = $alloc->invoice;
                if ($inv) {
                    $newPaid = max(0, (float) $inv->paid_amount - (float) $alloc->allocated_amount);
                    $newStatus = ($newPaid <= 0) ? 'approved' : 'partially_paid';
                    $inv->update([
                        'paid_amount' => $newPaid,
                        'status' => $newStatus,
                    ]);
                }
            }

            $num = $receipt->payment_number;
            $receipt->allocations()->delete();
            $receipt->delete();

            AuditLog::record('payment_deleted', 'receipts', "Reversed / deleted Receipt #{$num}");
        });

        return response()->json(['status' => true, 'message' => 'Receipt deleted and invoice allocations reversed.']);
    }

    public function print(Payment $receipt)
    {
        $receipt->load(['customer', 'allocations.invoice', 'createdBy']);
        $org = OrganizationSetting::current();

        return view('admin.receipts.print', compact('receipt', 'org'));
    }

    /**
     * AJAX endpoint: fetch unpaid/partially paid invoices for customer
     */
    public function fetchCustomerInvoices(Customer $customer)
    {
        $invoices = Invoice::where('customer_id', $customer->id)
            ->where('status', '!=', 'cancelled')
            ->whereRaw('grand_total > paid_amount')
            ->orderBy('invoice_date')
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->invoice_date->format('d/m/Y'),
                'grand_total' => (float) $inv->grand_total,
                'paid_amount' => (float) $inv->paid_amount,
                'outstanding' => (float) $inv->outstanding,
            ]);

        return response()->json([
            'invoices' => $invoices,
            'total_outstanding' => $invoices->sum('outstanding'),
        ]);
    }
}

