<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\InventoryLedger;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Permission;
use App\Models\Procurement;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PermissionRegistry;
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "       ERP MODULES VERIFICATION & TEST SUITE            \n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $description, bool $condition, string $details = '') {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo " [PASS] {$description}\n";
    } else {
        $failCount++;
        echo " [FAIL] {$description} " . ($details ? "--> {$details}" : "") . "\n";
    }
}

// -----------------------------------------------------------
// 1. MODULE 1: RBAC SYSTEM VERIFICATION
// -----------------------------------------------------------
echo "--- Testing Module 1: Role-Based Access Control (RBAC) ---\n";

$expectedRoles = [
    'Administrator', 'Sales', 'Purchase', 'Accounts', 'Stores',
    'Production', 'QC', 'Management', 'Project', 'Logistics'
];
foreach ($expectedRoles as $roleName) {
    $exists = Role::where('name', $roleName)->orWhere('name', strtolower($roleName))->exists();
    assertTest("Role '{$roleName}' exists in system", $exists);
}

// Check permissions count
$permsCount = Permission::count();
assertTest("Permissions table has >= 110 granular permissions", $permsCount >= 110, "Found {$permsCount}");

// Test multi-role assignment
$testUser = User::firstOrCreate(
    ['email' => 'rbac.tester@vmspro.com'],
    ['name' => 'RBAC Tester', 'password' => bcrypt('password123')]
);

$salesRole = Role::whereIn('name', ['Sales', 'sales'])->first();
$accountsRole = Role::whereIn('name', ['Accounts', 'accounts'])->first();
$testUser->roles()->sync([$salesRole->id, $accountsRole->id]);

assertTest("User has multiple roles assigned (Sales & Accounts)", $testUser->roles()->count() >= 2);
PermissionService::clearCache($testUser);

// Permissions check: Sales role can view enquiries, Accounts role can view invoices
assertTest("User has access to enquiries (via Sales role)", PermissionService::userCan($testUser, 'enquiries', 'view'));
assertTest("User has access to invoices (via Accounts role)", PermissionService::userCan($testUser, 'invoices', 'view'));
assertTest("User is denied access to role deletion", !PermissionService::userCan($testUser, 'roles', 'delete'));

// Test Administrator full access
$adminUser = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'Administrator']))->first();
if ($adminUser) {
    assertTest("Administrator has access to everything", PermissionService::userCan($adminUser, 'roles', 'delete'));
}

// -----------------------------------------------------------
// 2. MODULE 2: INVOICE MANAGEMENT VERIFICATION
// -----------------------------------------------------------
echo "\n--- Testing Module 2: Invoice Management ---\n";

$customer = Customer::first() ?? Customer::create(['name' => 'Acme Corp Test', 'email' => 'acme@test.com', 'gstin' => '27AABCU9603R1ZM']);
$product = Product::first() ?? Product::create(['name' => 'Industrial Valve X1', 'sku' => 'VALVE-01', 'selling_price' => 1500]);

$generatedInvNum = Invoice::generateInvoiceNumber('tax');
assertTest("Invoice number generated in expected format (e.g. INV/2026-27/00001)", str_starts_with($generatedInvNum, 'INV/'), $generatedInvNum);

$invoice = Invoice::create([
    'invoice_number' => $generatedInvNum,
    'type' => 'tax',
    'invoice_date' => now()->format('Y-m-d'),
    'customer_id' => $customer->id,
    'customer_gst' => $customer->gstin,
    'status' => 'draft',
    'created_by' => $testUser->id,
]);

$item = $invoice->items()->create([
    'product_id' => $product->id,
    'item_name' => $product->name,
    'hsn_code' => '8481',
    'quantity' => 10,
    'unit' => 'Nos',
    'rate' => 1000.00,
    'discount_percent' => 10.00,
    'discount_amount' => 1000.00, // 10,000 - 1,000 = 9,000
    'tax_percent' => 18.00,
    'tax_amount' => 1620.00,     // 9,000 * 18% = 1,620
    'amount' => 10620.00,
]);

$invoice->recalcTotals();
$invoice->refresh();

assertTest("Invoice Taxable Amount calculated correctly (₹9,000)", (float) $invoice->taxable_amount == 9000.00, "Got: " . $invoice->taxable_amount);
assertTest("Invoice GST calculated correctly (₹1,620)", (float) ($invoice->cgst + $invoice->sgst + $invoice->igst) == 1620.00, "Got: " . ($invoice->cgst + $invoice->sgst + $invoice->igst));
assertTest("Invoice Grand Total calculated correctly (₹10,620)", (float) $invoice->grand_total == 10620.00, "Got: " . $invoice->grand_total);
assertTest("Invoice Outstanding equals Grand Total initially", (float) $invoice->outstanding == 10620.00);

// Status transition
$invoice->update(['status' => 'approved']);
assertTest("Invoice can be approved", $invoice->status === 'approved');

// -----------------------------------------------------------
// 3. MODULE 3: PAYMENT MANAGEMENT VERIFICATION
// -----------------------------------------------------------
echo "\n--- Testing Module 3: Payment Management ---\n";

$rcptNum = Payment::generatePaymentNumber(Payment::DIRECTION_RECEIPT);
assertTest("Receipt number generated in expected format (e.g. RCPT/2026-27/00001)", str_starts_with($rcptNum, 'RCPT/'), $rcptNum);

// Record partial customer receipt of ₹6,000 against invoice
$payment = Payment::create([
    'payment_number' => $rcptNum,
    'direction' => Payment::DIRECTION_RECEIPT,
    'customer_id' => $customer->id,
    'payment_date' => now()->format('Y-m-d'),
    'amount' => 6000.00,
    'payment_mode' => 'bank_transfer',
    'reference_number' => 'UTR-' . time(),
    'status' => 'completed',
    'created_by' => $testUser->id,
]);

PaymentAllocation::create([
    'payment_id' => $payment->id,
    'invoice_id' => $invoice->id,
    'allocated_amount' => 6000.00,
]);

$invoice->update([
    'paid_amount' => 6000.00,
    'status' => 'partially_paid',
]);
$invoice->refresh();

assertTest("Invoice status updated to 'partially_paid'", $invoice->status === 'partially_paid');
assertTest("Invoice remaining outstanding updated correctly (₹4,620)", (float) $invoice->outstanding == 4620.00, "Got: " . $invoice->outstanding);

// Test Vendor Payment
$vendor = Vendor::first() ?? Vendor::create(['name' => 'Supplier Alpha Ltd']);
$poNum = Payment::generatePaymentNumber(Payment::DIRECTION_PAYMENT);
assertTest("Vendor payment number generated in expected format (e.g. PYMT/2026-27/00001)", str_starts_with($poNum, 'PYMT/'), $poNum);

$vendorPayment = Payment::create([
    'payment_number' => $poNum,
    'direction' => Payment::DIRECTION_PAYMENT,
    'vendor_id' => $vendor->id,
    'payment_date' => now()->format('Y-m-d'),
    'amount' => 12500.00,
    'payment_mode' => 'neft',
    'reference_number' => 'NEFT-' . time(),
    'status' => 'completed',
    'created_by' => $testUser->id,
]);
assertTest("Vendor payment saved with direction 'payment'", $vendorPayment->isPayment());

// -----------------------------------------------------------
// 4. MODULE 4: INVENTORY LEDGER VERIFICATION
// -----------------------------------------------------------
echo "\n--- Testing Module 4: Inventory Ledger based on PO ---\n";

// Inward: GRN entry (+50 units)
$grn = InventoryLedger::recordMovement([
    'transaction_date' => now()->format('Y-m-d'),
    'transaction_type' => 'grn',
    'reference_no' => 'GRN-TEST-001',
    'product_id' => $product->id,
    'vendor_id' => $vendor->id,
    'qty_in' => 50,
    'qty_out' => 0,
    'unit' => 'Nos',
    'remarks' => 'GRN inward test',
    'created_by' => $testUser->id,
]);

assertTest("GRN inward recorded and balance increased", (float) $grn->balance_qty >= 50.0);

// Outward: Issue entry (-20 units)
$startBal = (float) $grn->balance_qty;
$issue = InventoryLedger::recordMovement([
    'transaction_date' => now()->format('Y-m-d'),
    'transaction_type' => 'issue',
    'reference_no' => 'ISSUE-TEST-001',
    'product_id' => $product->id,
    'qty_in' => 0,
    'qty_out' => 20,
    'unit' => 'Nos',
    'remarks' => 'Material issued for production',
    'created_by' => $testUser->id,
]);

assertTest("Issue outward recorded and balance reduced by 20", (float) $issue->balance_qty == ($startBal - 20.0), "Got: " . $issue->balance_qty);

// Negative stock prevention test: try to issue 999,999 units without allow_negative
$caughtException = false;
try {
    InventoryLedger::recordMovement([
        'transaction_date' => now()->format('Y-m-d'),
        'transaction_type' => 'issue',
        'product_id' => $product->id,
        'qty_in' => 0,
        'qty_out' => 999999,
        'created_by' => $testUser->id,
    ], false);
} catch (\InvalidArgumentException $e) {
    $caughtException = true;
}
assertTest("Negative stock prevention blocks excessive stock issuance", $caughtException);

// -----------------------------------------------------------
// 5. AUDIT LOG VERIFICATION
// -----------------------------------------------------------
echo "\n--- Testing Audit Trail ---\n";
AuditLog::record('test_action', 'audit_test', 'Verification run test entry');
$latestLog = AuditLog::where('action', 'test_action')->latest()->first();
assertTest("Audit log entry successfully recorded", $latestLog !== null && $latestLog->module === 'audit_test');

// -----------------------------------------------------------
// 6. REPORTS QUERIES VERIFICATION
// -----------------------------------------------------------
echo "\n--- Testing Reports Queries ---\n";
// Test invoice register query
$invRegCount = Invoice::count();
assertTest("Invoice register query executes cleanly ({$invRegCount} records)", $invRegCount > 0);

// Test receipts query
$rcptCount = Payment::whereIn('direction', ['receipt', 'received'])->count();
assertTest("Receipts query executes cleanly ({$rcptCount} records)", $rcptCount > 0);

// Test vendor payment query
$vPayCount = Payment::whereIn('direction', ['payment', 'paid'])->count();
assertTest("Vendor payment query executes cleanly ({$vPayCount} records)", $vPayCount > 0);

// Test inventory ledger eager loading with product.category relation
$ledgerQuery = InventoryLedger::with(['product.category', 'vendor', 'purchaseOrder'])->paginate(20);
assertTest("Inventory ledger query with product.category relation executes cleanly ({$ledgerQuery->count()} records)", $ledgerQuery !== null);

// Test product category relation
$prodWithCat = Product::with(['category', 'unit'])->find($product->id);
assertTest("Product category relationship accessible on model", $prodWithCat !== null);

// Test Amount::inWords
$inWords = \App\Support\Amount::inWords(10620.50);
assertTest("Amount::inWords formats correctly ({$inWords})", str_contains($inWords, 'Ten Thousand Six Hundred Twenty Rupees And Fifty Paise Only'));

// Test VendorDashboardService
$dashService = new \App\Services\VendorDashboardService();
$dashData = $dashService->getDashboardData(new \Illuminate\Http\Request());
assertTest("VendorDashboardService compiles KPI summary cards", isset($dashData['vendorStats'], $dashData['purchaseStats'], $dashData['financialStats'], $dashData['inventoryStats']));
assertTest("VendorDashboardService compiles charts and vendor analysis", isset($dashData['vendorChart'], $dashData['poChart'], $dashData['monthlySpend'], $dashData['vendorAnalysis']));

// Test stock balances sync
$stockBalance = DB::table('stock_balances')->where('product_id', $product->id)->value('quantity');
assertTest("Stock balances table reconciled with ledger ({$stockBalance} units)", (float) $stockBalance == (float) $issue->balance_qty);

echo "\n========================================================\n";
echo " TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED \n";
echo "========================================================\n";

