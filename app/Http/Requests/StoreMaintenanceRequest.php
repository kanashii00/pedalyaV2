<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bicycle_id'     => 'required|exists:bicycles,id',
            'type'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'severity'       => 'nullable|string|in:low,medium,high,critical',
            'estimated_cost' => 'nullable|numeric|min:0',
            'technician'     => 'nullable|string|max:255',
            'scheduled_date' => 'nullable|date',
            'notes'          => 'nullable|string',
        ];
    }
}
