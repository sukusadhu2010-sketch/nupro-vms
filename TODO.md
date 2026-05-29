# Vendor Payments + Ledger System

## Previous Task ✅ Complete

## New Task: Vendor Payments + Bank Ledger

### Plan:
1. Unified Payments table (polymorphic Invoice/Procurement)
2. Ledgers table for balance tracking  
3. Procurement payment UI (copy invoice pattern)
4. Ledger update logic (cash/UPI/bank/post_office)
5. Migrate existing invoice payments

### Steps:

### [x] Step 1: Create payments migration (unified) ✅
File: `database/migrations/2026_05_04_195353_create_payments_table.php` ✅

### [x] Step 2: Create ledgers migration ✅
File: `database/migrations/2026_05_04_195513_create_ledgers_table.php` ✅


### [x] Step 3: Create Payment/Ledger models ✅
- `app/Models/Payment.php` - polymorphic payable, accessors ✅
- `app/Models/Ledger.php` - balance tracking ✅

### [x] Step 4: Update Procurement model + controller ✅
Files: `app/Models/Procurement.php` (payments relation) ✅
`app/Models/Invoice.php` (payments unified) ✅

### [x] Step 5: Procurement payment views ✅
`resources/views/admin/procurements/payment-entry.blade.php` ✅
`resources/views/admin/procurements/show.blade.php` (button + history) ✅

### [x] Step 6: Routes + ledger service logic ✅
`app/Services/LedgerService.php` - payment recording + balance updates ✅

### [ ] Step 7: Migrate InvoicePayment → unified Payments + seed ledgers

**Next:** Step 1 - Payments migration
