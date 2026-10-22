# SD/PBG Register — Excel Export

## Overview
The SD/PBG (Security Deposit / Performance Bank Guarantee) Register lists Bank
Guarantee records linked to Sales Orders flagged **SD/PBG = Yes**, together with
their optional invoice details. The register page supports filtering and a
server-side Excel export.

## Endpoints

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/sd-pbg-register` | `sd-pbg.index` | Register list page (paginated, filterable) |
| GET | `/sd-pbg-register/export/excel` | `sd-pbg.export.excel` | Excel export (streamed download) |

Both routes require authentication.

## Export behaviour

- **Core rule:** the query always applies `sales_orders.sd_pbg = 1`. Records where
  SD/PBG = No or blank are never exported.
- **Filters** (all optional, combinable): `client`, `bg_number`, `po_no`,
  `invoice_no`, `status`, `bg_date_from/to`, `expiry_from/to`, `claim_from/to`.
- **Empty result:** responds `404` with JSON
  `{"message": "No SD/PBG records available for export."}`.
- **Errors:** any exception is logged (`Log::error`) and returned as a JSON 500
  message; the frontend shows it as an error toast.
- **File name:** `SD_PBG_Register_YYYYMMDD_HHMMSS.xlsx`.
- **Duplicate prevention:** the button is disabled while a request is in flight.

## Excel format

- Title row `SD/PBG Register Report`, generated date/time, logged-in user name
  and total record count.
- Columns (exact order): SL No, Client, BG No., BG Date, BG Expiry Date,
  Claim Date, Claim Amount, PO No., PO Date, Invoice No., Invoice Date, Status.
- Bold headers, freeze pane below header row, Excel auto-filter, borders on all
  data cells, alternate row shading, auto-fit column widths.
- Dates (`BG Date`, `BG Expiry Date`, `Claim Date`, `PO Date`, `Invoice Date`)
  rendered as `DD-MM-YYYY`; `Claim Amount` as `#,##0.00`.

## Key files

- `app/Http/Controllers/Admin/SdPbgRegisterController.php` — list + export endpoints
- `app/Services/SdPbgExportService.php` — workbook generation
- `app/Models/BankGuarantee.php` — BG model (belongs to SalesOrder + Invoice)
- `app/Models/Invoice.php` — invoice model (table exists in production; the
  bank-guarantees migration creates it on fresh/test databases)
- `database/migrations/2026_10_12_000001_create_bank_guarantees_table.php`
- `resources/views/admin/sd-pbg/index.blade.php` — register UI + export JS
- `tests/Unit/SdPbgExportTest.php` — 9 unit/integration tests

## Usage (frontend)

```js
// Export button (rendered in the view) performs:
const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
// 404 → "No SD/PBG records available for export." toast
// 200 → blob download + success toast; loader closed in finally
```

## Tests

Run: `php artisan test tests/Unit/SdPbgExportTest.php`

Covers: SD/PBG=Yes filtering, exact column order, sequential SL No, title/meta
rows, amount + date formats, empty dataset, streamed download with correct
filename, filter-aware export, 404 message, and authentication guard.
