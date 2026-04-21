<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Fine-grained author-vs-manager check is enforced inside the service.
        // Here we only gate on having at least view access (authenticated).
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'employee_id'  => ['required', 'integer', 'exists:employees,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'payment_date' => ['required', 'date'],
        ];
    }
}
