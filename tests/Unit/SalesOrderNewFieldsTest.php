<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\JobNumberService;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SalesOrderNewFieldsTest extends TestCase
{
    use RefreshDatabase;

    private SalesOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SalesOrderService::class);
    }

    private function makeQuotation(): Quotation
    {
        $customer = Customer::create([
            'name' => 'Acme Corp',
            'email' => 'acme' . uniqid() . '@example.com',
            'address' => '1 Test Street',
        ]);

        $enquiry = Enquiry::create(['customer_id' => $customer->id, 'enquiry_number' => 'ENQ-' . uniqid()]);

        return Quotation::create([
            'enquiry_id' => $enquiry->id,
            'quote_number' => 'Q-' . uniqid(),
            'status' => 'accepted',
            'total_amount' => 1000.00,
        ]);
    }

    /** @test */
    public function job_number_is_generated_in_correct_format(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation());

        $this->assertMatchesRegularExpression(
            '/^JOB\d{4}-\d{4}$/',
            $order->job_number,
            'Job Number must match JOB<FinancialYear>-<Sequence> format.'
        );

        $expectedPrefix = 'JOB' . app(JobNumberService::class)->financialYear() . '-';
        $this->assertStringStartsWith($expectedPrefix, $order->job_number);
    }

    /** @test */
    public function job_number_sequence_increments_correctly(): void
    {
        $first = $this->service->convertToOrder($this->makeQuotation());
        $second = $this->service->convertToOrder($this->makeQuotation());

        $firstSeq = (int) substr($first->job_number, -4);
        $secondSeq = (int) substr($second->job_number, -4);

        $this->assertSame(1, $firstSeq);
        $this->assertSame($firstSeq + 1, $secondSeq);
    }

    /** @test */
    public function job_numbers_are_unique(): void
    {
        $numbers = collect(range(1, 5))->map(fn () => $this->service->convertToOrder($this->makeQuotation())->job_number);

        $this->assertCount(5, $numbers->unique());
    }

    /** @test */
    public function explicit_job_number_is_still_honoured_for_backward_compatibility(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation(), ['job_number' => 'JOB2627-9001']);

        $this->assertSame('JOB2627-9001', $order->job_number);
    }

    /** @test */
    public function customer_po_number_and_po_date_are_saved(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation(), [
            'customer_po_number' => 'CPO-2026-0042',
            'customer_po_date' => '2026-09-15',
        ]);

        $this->assertSame('CPO-2026-0042', $order->customer_po_number);
        $this->assertSame('2026-09-15', $order->customer_po_date->format('Y-m-d'));

        $fresh = SalesOrder::find($order->id);
        $this->assertSame('CPO-2026-0042', $fresh->customer_po_number);
        $this->assertSame('2026-09-15', $fresh->customer_po_date->format('Y-m-d'));
    }

    /** @test */
    public function mtc_and_pdi_values_are_stored_and_retrieved(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation(), [
            'mtc' => true,
            'pdi' => false,
        ]);

        $this->assertTrue($order->mtc);
        $this->assertFalse($order->pdi);

        $fresh = SalesOrder::find($order->id);
        $this->assertTrue((bool) $fresh->mtc);
        $this->assertFalse((bool) $fresh->pdi);
    }

    /** @test */
    public function delivery_target_date_is_persisted(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation(), [
            'delivery_target_date' => '2026-11-30',
        ]);

        $this->assertSame('2026-11-30', $order->delivery_target_date->format('Y-m-d'));

        $fresh = SalesOrder::find($order->id);
        $this->assertSame('2026-11-30', $fresh->delivery_target_date->format('Y-m-d'));
    }

    /** @test */
    public function payment_mode_accepts_only_allowed_values_in_model_persistence(): void
    {
        foreach (SalesOrder::PAYMENT_MODES as $mode) {
            $order = $this->service->convertToOrder($this->makeQuotation(), ['payment_mode' => $mode]);
            $this->assertSame($mode, SalesOrder::find($order->id)->payment_mode);
        }
    }

    /** @test */
    public function payment_mode_validation_rejects_invalid_values(): void
    {
        $this->expectException(ValidationException::class);

        validator(
            ['payment_mode' => 'cash-on-delivery'],
            ['payment_mode' => 'in:' . implode(',', SalesOrder::PAYMENT_MODES)]
        )->validate();
    }

    /** @test */
    public function existing_sales_orders_without_new_fields_remain_valid(): void
    {
        // Simulate a legacy order created before this change (nulls allowed).
        $order = $this->service->convertToOrder($this->makeQuotation());

        $legacy = SalesOrder::find($order->id);
        $legacy->forceFill([
            'customer_po_number' => null,
            'customer_po_date' => null,
            'delivery_target_date' => null,
            'payment_mode' => null,
        ])->save();

        $fresh = SalesOrder::find($order->id);

        $this->assertNull($fresh->customer_po_number);
        $this->assertNull($fresh->customer_po_date);
        $this->assertNull($fresh->delivery_target_date);
        $this->assertNull($fresh->payment_mode);
        $this->assertSame('draft', $fresh->status);
        $this->assertNotNull($fresh->order_number);
    }

    /** @test */
    public function payment_mode_label_is_resolved(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation(), ['payment_mode' => 'advance_pi']);

        $this->assertSame('Advance + PI', $order->payment_mode_label);
        $this->assertSame('—', (new SalesOrder())->payment_mode_label);
    }

    /** @test */
    public function financial_year_is_calculated_correctly(): void
    {
        $service = app(JobNumberService::class);

        $this->assertSame('2627', $service->financialYear(new \DateTime('2026-10-03')));
        $this->assertSame('2526', $service->financialYear(new \DateTime('2026-03-31')));
        $this->assertSame('2627', $service->financialYear(new \DateTime('2027-03-31')));
        $this->assertSame('2728', $service->financialYear(new \DateTime('2027-04-01')));
    }

    /** @test */
    public function credit_days_is_saved_for_lc_and_credit_payment_modes(): void
    {
        foreach (['lc', 'credit'] as $mode) {
            $order = $this->service->convertToOrder($this->makeQuotation(), [
                'payment_mode' => $mode,
                'credit_days' => 45,
            ]);

            $this->assertSame(45, SalesOrder::find($order->id)->credit_days);
        }
    }

    /** @test */
    public function credit_days_validation_requires_days_for_lc_and_credit(): void
    {
        foreach (['lc', 'credit'] as $mode) {
            $this->expectException(ValidationException::class);

            validator(
                ['payment_mode' => $mode, 'credit_days' => null],
                ['payment_mode' => 'in:' . implode(',', SalesOrder::PAYMENT_MODES),
                 'credit_days' => 'nullable|integer|min:1|max:365|required_if:payment_mode,lc,credit']
            )->validate();
        }
    }

    /** @test */
    public function credit_days_is_not_required_for_other_payment_modes(): void
    {
        validator(
            ['payment_mode' => 'advance_pi', 'credit_days' => null],
            ['payment_mode' => 'in:' . implode(',', SalesOrder::PAYMENT_MODES),
             'credit_days' => 'nullable|integer|min:1|max:365|required_if:payment_mode,lc,credit']
        )->validate();

        $this->assertTrue(true);
    }

    /** @test */
    public function credit_days_rejects_out_of_range_values(): void
    {
        $this->expectException(ValidationException::class);

        validator(
            ['payment_mode' => 'credit', 'credit_days' => 400],
            ['payment_mode' => 'in:' . implode(',', SalesOrder::PAYMENT_MODES),
             'credit_days' => 'nullable|integer|min:1|max:365|required_if:payment_mode,lc,credit']
        )->validate();
    }

    /** @test */
    public function credit_days_required_helper_reflects_payment_mode(): void
    {
        $order = $this->service->convertToOrder($this->makeQuotation(), [
            'payment_mode' => 'lc',
            'credit_days' => 30,
        ]);

        $this->assertTrue($order->isCreditDaysRequired());
        $this->assertSame('LC (Letter of Credit) (30 days)', $order->payment_mode_label_with_days);

        $other = $this->service->convertToOrder($this->makeQuotation(), ['payment_mode' => 'proforma_invoice']);
        $this->assertFalse($other->isCreditDaysRequired());
    }
}
