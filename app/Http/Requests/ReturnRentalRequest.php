<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_lat'       => 'nullable|numeric|between:-90,90',
            'return_lng'       => 'nullable|numeric|between:-180,180',
            'payment_method'   => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'notes'            => 'nullable|string',
        ];
    }
}
