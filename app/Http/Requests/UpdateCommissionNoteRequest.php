<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Delegates to CommissionNotePolicy::update(), which enforces:
        // "only the original author OR a user with 'manage commission notes'".
        // The service layer re-checks this rule as well (belt-and-suspenders).
        return $this->user()->can('update', $this->route('commissionNote'));
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
