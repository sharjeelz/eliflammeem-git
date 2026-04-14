<?php

namespace App\Http\Requests\Public;

use App\Rules\ValidTurnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnifiedSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAnonymous = $this->boolean('anonymous');

        return [
            // Honeypot — bots fill this, humans don't see it
            'website'              => ['present', 'max:0'],
            // Turnstile token
            'cf-turnstile-response' => [app()->environment('local', 'testing') ? 'nullable' : 'required', new ValidTurnstile],
            'anonymous'            => ['nullable', 'boolean'],
            // Accepts either a real access code OR the contact's external_id (student/school ID)
            'code'                 => [
                Rule::requiredIf(! $isAnonymous),
                'nullable',
                'string',
                'max:100',
            ],
            'title'                => ['nullable', 'string', 'max:180'],
            'description'          => ['required', 'string', 'min:10', 'max:8000'],
            'category_id'          => ['nullable', 'integer', Rule::exists('issue_categories', 'id')->where(fn ($q) => $q->where('tenant_id', tenant('id')))],
            'attachments'          => ['nullable', 'array', 'max:5'],
            'attachments.*'        => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'                  => 'An access code is required unless you submit anonymously.',
            'code.exists'                    => 'Invalid access code.',
            'website.max'                    => 'Bot detected.',
            'cf-turnstile-response.required' => 'Please complete the security check.',
            'description.min'                => 'Please write at least 10 characters.',
        ];
    }
}
