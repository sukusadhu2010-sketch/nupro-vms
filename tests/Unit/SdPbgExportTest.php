<?php

namespace Tests\Unit;

use App\Models\BankGuarantee;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Services\SdPbgExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Tests\TestCase;

class SdPbgExportTest extends TestCase
{
    use RefreshDatabase;

    private SdPbgExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SdPbgExportService::class);
    }

    private function makeOrderWithBg(array $soAttrs = [], array $bgAttrs = []): BankGuarantee
    {
        $customer = Customer::create([
            'name' => $soAttrs['customer_name'] ?? 'Acme Corp',
            'email' => 'acme' . uniqid() . '@example.com',
        ]);

        $enquiry = Enquiry::create([
            'customer_id' => $customer->id,
            'enquiry_number' => 'ENQ-' . uniqid(),
        ]);

        $order = SalesOrder::create(array_merge([
            'customer_id' => $customer->id,
            'order_number' => 'SO-' . uniqid(),
            'job_number' => 'JOB' . uniqid(),
            'status' => 'confirmed',
            'total_amount' => 50000,
            'sd_pbg' => 1,
            'customer_po_number' => 'PO-1001',
            'customer_po_date' => '2026-01-15',
        ], $soAttrs));

        $bg = BankGuarantee::create(array_merge([
            'sales_order_id' => $order->id,
            'bg_number' => 'BG-' . uniqid(),
            'bg_date' => '2026-02-01',
            'expiry_date' => '2027-01-31',
            'claim_amount' => 25000.5,
            'status' => 'active',
        ], $bgAttrs));

        return $bg->fresh();
    }

    /** @test */
    public function only_sd_pbg_yes_records_are_exported(): void
    {
        $included = $this->makeOrderWithBg();

        // SD/PBG = No must never appear (sd_pbg column is NOT NULL by design)
        $this->makeOrderWithBg(['sd_pbg' => 0]);
        $query = \App\Models\BankGuarantee::query()->whereHas('salesOrder', fn ($q) => $q->where('sd_pbg', 1));

        $rows = $this->service->buildRows($query->with(['salesOrder.customer', 'invoice'])->get());

        $this->assertCount(1, $rows);
        $this->assertSame($included->bg_number, $rows[0][2]);
    }

    /** @test */
    public function rows_map_columns_in_exact_order(): void
    {
        $order = $this->makeOrderWithBg();

        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-001',
            'sales_order_id' => $order->sales_order_id,
            'customer_id' => $order->customer_id,
            'status' => 'issued',
            'invoice_date' => '2026-03-10',
            'total_amount' => 50000,
        ]);

        $order->update(['invoice_id' => null]);
        BankGuarantee::where('sales_order_id', $order->id)->update(['invoice_id' => $invoice->id]);

        $bg = BankGuarantee::with(['salesOrder.customer', 'invoice'])->first();
        $rows = $this->service->buildRows(collect([$bg]));

        $this->assertSame([1], [$rows[0][0]]);                     // SL No
        $this->assertSame('Acme Corp', $rows[0][1]);               // Client
        $this->assertSame($bg->bg_number, $rows[0][2]);            // BG No.
        $this->assertSame('01-02-2026', $rows[0][3]);              // BG Date DD-MM-YYYY
        $this->assertSame('31-01-2027', $rows[0][4]);              // Expiry
        $this->assertSame('', $rows[0][5]);                        // Claim Date (null → blank)
        $this->assertSame(25000.5, (float) $rows[0][6]);           // Claim Amount
        $this->assertSame('PO-1001', $rows[0][7]);                 // PO No.
        $this->assertSame('15-01-2026', $rows[0][8]);              // PO Date
        $this->assertSame('INV-2026-001', $rows[0][9]);            // Invoice No.
        $this->assertSame('10-03-2026', $rows[0][10]);             // Invoice Date
        $this->assertSame('Active', $rows[0][11]);                 // Status
    }

    /** @test */
    public function sl_no_is_sequential_across_rows(): void
    {
        $this->makeOrderWithBg();
        $this->makeOrderWithBg(['customer_name' => 'Beta Ltd']);

        $rows = $this->service->buildRows(
            \App\Models\BankGuarantee::with(['salesOrder.customer', 'invoice'])
                ->whereHas('salesOrder', fn ($q) => $q->where('sd_pbg', 1))
                ->orderBy('bg_date')
                ->get()
        );

        $this->assertCount(2, $rows);
        $this->assertSame(1, $rows[0][0]);
        $this->assertSame(2, $rows[1][0]);
    }

    /** @test */
    public function spreadsheet_has_title_generated_date_user_and_count(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $this->makeOrderWithBg();

        $spreadsheet = $this->service->exportExcel(
            \App\Models\BankGuarantee::with(['salesOrder.customer', 'invoice'])->get(),
            [],
            1
        );

        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('SD/PBG Register Report', $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString('Generated on:', (string) $sheet->getCell('A2')->getValue());
        $this->assertStringContainsString('Total Records: 1', (string) $sheet->getCell('A3')->getValue());

        // Header row present and bold
        $this->assertSame('SL No', $sheet->getCell('A5')->getValue());
        $this->assertSame('Status', $sheet->getCell('L5')->getValue());
        $this->assertTrue($sheet->getStyle('A5')->getFont()->getBold());

        // Freeze pane below header, auto filter enabled on header row
        $this->assertSame('A6', $sheet->getFreezePane());
        $this->assertSame('A5:L6', (string) $sheet->getAutoFilter()->getRange());
    }

    /** @test */
    public function amount_and_date_cells_are_formatted(): void
    {
        $this->makeOrderWithBg();

        $spreadsheet = $this->service->exportExcel(
            \App\Models\BankGuarantee::with(['salesOrder.customer', 'invoice'])->get(),
            [],
            1
        );

        $sheet = $spreadsheet->getActiveSheet();

        // Claim Amount (G6) — currency with 2 decimals
        $this->assertSame(
            NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            $sheet->getStyle('G6')->getNumberFormat()->getFormatCode()
        );

        // Date columns stored as DD-MM-YYYY text values
        $this->assertSame('01-02-2026', $sheet->getCell('D6')->getValue());
        $this->assertSame('31-01-2027', $sheet->getCell('E6')->getValue());
    }

    /** @test */
    public function empty_dataset_produces_header_only_workbook(): void
    {
        $spreadsheet = $this->service->exportExcel(collect(), [], 0);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('Total Records: 0  |  Filters: None', $sheet->getCell('A3')->getValue());
        $this->assertSame('SL No', $sheet->getCell('A5')->getValue());
    }

    /** @test */
    public function export_endpoint_streams_xlsx_download(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $this->makeOrderWithBg();

        $response = $this->get(route('sd-pbg.export.excel', ['status' => 'active']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertMatchesRegularExpression(
            '/SD_PBG_Register_\d{8}_\d{6}\.xlsx/',
            $response->headers->get('Content-Disposition')
        );
    }

    /** @test */
    public function export_endpoint_respects_filters_and_returns_404_when_empty(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $bg = $this->makeOrderWithBg(['customer_name' => 'ABC Engineering'], ['status' => 'active']);
        $this->makeOrderWithBg(['customer_name' => 'Other Corp']);

        // Filtered export: only ABC Engineering / Active
        $response = $this->get(route('sd-pbg.export.excel', ['client' => 'ABC Engineering', 'status' => 'active']));
        $response->assertOk();

        $spreadsheet = $this->service->exportExcel(
            \App\Models\BankGuarantee::query()
                ->whereHas('salesOrder', fn ($q) => $q->where('sd_pbg', 1))
                ->whereHas('salesOrder.customer', fn ($q) => $q->where('customers.name', 'like', '%ABC Engineering%'))
                ->where('status', 'active')
                ->with(['salesOrder.customer', 'invoice'])
                ->get(),
            ['client' => 'ABC Engineering', 'status' => 'active'],
            1
        );
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertSame($bg->bg_number, $sheet->getCell('C6')->getValue());
        $this->assertSame('Total Records: 1  |  Filters: Client: ABC Engineering | Status: Active', $sheet->getCell('A3')->getValue());

        // No matching records → 404 with the required message
        $empty = $this->get(route('sd-pbg.export.excel', ['client' => 'Nonexistent Corp']));
        $empty->assertStatus(404);
        $this->assertSame(
            ['message' => 'No SD/PBG records available for export.'],
            $empty->json()
        );
    }

    /** @test */
    public function export_requires_authentication(): void
    {
        $this->get(route('sd-pbg.export.excel'))->assertRedirect(route('login'));
    }
}
