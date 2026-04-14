<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth is handled by AuthenticateTenantApiKey middleware
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'role'        => ['required', 'in:parent,teacher'],
            'email'       => ['nullable', 'email', 'max:191', 'required_without:phone'],
            'phone'       => ['nullable', 'string', 'max:30', 'required_without:email'],
            'external_id' => ['nullable', 'string', 'max:100'],
            'branch_id'   => ['nullable', 'integer'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'At least one of email or phone is required.',
            'phone.required_without' => 'At least one of email or phone is required.',
        ];
    }

    /**
     * Override to return JSON 422 instead of a redirect.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'error'   => 'Validation failed.',
                'details' => $validator->errors(),
            ], 422)
        );
    }
}
