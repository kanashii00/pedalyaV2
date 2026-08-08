<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBicycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'serial_number' => 'required|string|unique:bicycles,serial_number',
            'model'         => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'current_lat'   => 'nullable|numeric|between:-90,90',
            'current_lng'   => 'nullable|numeric|between:-180,180',
            'battery_level' => 'nullable|numeric|between:0,100',
        ];
    }
}
