<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrganizationSetting;
use App\Models\Payment;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VendorPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['vendor', 'purchaseOrder'])
            ->where(function ($q) {
                $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid');
            })
            ->latest('payment_date');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
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
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        $metrics = [
            'total_paid' => Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))->sum('amount'),
            'this_month' => Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'total_po_value' => Procurement::where('status', '!=', 'cancelled')->sum('total_amount'),
        ];

        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        return view('admin.vendor-payments.index', compact('payments', 'metrics', 'vendors'));
    }

    public function create(Request $request)
    {
        $paymentNumber = Payment::generatePaymentNumber(Payment::DIRECTION_PAYMENT);
        $vendors = Vendor::orderBy('name')->get();
        $procurements = Procurement::with('vendor')->latest()->get();
        $selectedVendorId = $request->input('vendor_id');
        $selectedPoId = $request->input('purchase_order_id');

        return view('admin.vendor-payments.create', compact('paymentNumber', 'vendors', 'procurements', 'selectedVendorId', 'selectedPoId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_number' => 'required|string|unique:payments,payment_number',
            'payment_date' => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_order_id' => 'nullable|exists:procurements,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,bank_transfer,cheque,neft,rtgs,upi',
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('payments')->where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)),
            ],
            'bank_name' => 'nullable|string|max:100',
            'cheque_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $payment = DB::transaction(function () use ($validated) {
            $payment = Payment::create([
                'payment_number' => $validated['payment_number'],
                'direction' => Payment::DIRECTION_PAYMENT,
                'vendor_id' => $validated['vendor_id'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
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

            AuditLog::record('payment_created', 'vendor-payments', "Paid ₹{$payment->amount} to vendor {$payment->vendor?->name} (Payment #{$payment->payment_number})");

            return $payment;
        });

        return redirect()->route('vendor-payments.show', $payment)
            ->with('success', "Vendor Payment {$payment->payment_number} recorded successfully.");
    }

    public function show(Payment $vendorPayment)
    {
        $vendorPayment->load(['vendor', 'purchaseOrder', 'createdBy']);
        $org = OrganizationSetting::current();

        return view('admin.vendor-payments.show', compact('vendorPayment', 'org'));
    }

    public function destroy(Payment $vendorPayment)
    {
        $num = $vendorPayment->payment_number;
        $vendorPayment->delete();
        AuditLog::record('payment_deleted', 'vendor-payments', "Deleted Vendor Payment #{$num}");

        return response()->json(['status' => true, 'message' => 'Vendor payment record deleted.']);
    }

    public function print(Payment $vendorPayment)
    {
        $vendorPayment->load(['vendor', 'purchaseOrder', 'createdBy']);
        $org = OrganizationSetting::current();

        return view('admin.vendor-payments.print', compact('vendorPayment', 'org'));
    }

    public function fetchVendorOrders(Vendor $vendor)
    {
        $orders = Procurement::where('vendor_id', $vendor->id)
            ->latest('order_date')
            ->get(['id', 'po_number', 'order_date', 'total_amount', 'status']);

        return response()->json(['orders' => $orders]);
    }
}

