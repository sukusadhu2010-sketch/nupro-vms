<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SalesOrder;
use Illuminate\Validation\Rule;

class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_id' => [
                'required',
                'integer',
                Rule::exists('quotations', 'id')->where(function ($query) {
                    $query->where('status', '!=', 'converted');
                }),
            ],
            'customer_po_number' => ['nullable', 'string', 'max:255'],
            'customer_po_date' => ['nullable', 'date'],
            'mtc' => ['nullable', 'boolean'],
            'pdi' => ['nullable', 'boolean'],
            'delivery_target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'payment_mode' => ['nullable', Rule::in(SalesOrder::PAYMENT_MODES)],
            'credit_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
                Rule::requiredIf(fn () => in_array($this->input('payment_mode'), ['lc', 'credit'], true)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_id.exists' => 'The selected quotation does not exist or has already been converted.',
            'payment_mode.in' => 'The selected payment mode is invalid. Allowed values: ' . implode(', ', array_keys(SalesOrder::PAYMENT_MODE_LABELS)) . '.',
            'credit_days.required' => 'Number of days is required for LC and Credit payment modes.',
        ];
    }
}

