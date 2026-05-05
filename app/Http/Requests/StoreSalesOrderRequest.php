<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_id.exists' => 'The selected quotation does not exist or has already been converted.',
        ];
    }
}

