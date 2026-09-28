<?php

// app/Http/Requests/ReportDamagedRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportDamagedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'note'       => ['required', 'string', 'max:500'],
        ];
    }
}
