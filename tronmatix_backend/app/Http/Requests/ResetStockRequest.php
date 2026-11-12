<?php

// app/Http/Requests/ResetStockRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'all' → every product; 'category' → products in a named category
            'scope'    => ['required', 'in:all,category'],
            'category' => ['required_if:scope,category', 'string', 'max:100'],
            'note'     => ['nullable', 'string', 'max:500'],
        ];
    }
}