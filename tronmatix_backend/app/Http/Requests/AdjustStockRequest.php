<?php

// app/Http/Requests/AdjustStockRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'        => ['required', 'integer', 'exists:products,id'],
            'counted_quantity'  => ['required', 'integer', 'min:0'],
            'note'              => ['nullable', 'string', 'max:500'],
        ];
    }
}
