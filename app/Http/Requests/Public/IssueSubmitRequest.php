<?php

namespace App\Http\Requests\Public;

use App\Rules\ValidTurnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Honeypot — bots fill this, humans don't see it
            'website' => ['present', 'max:0'],
            // Turnstile token
            'cf-turnstile-response' => [app()->environment('local', 'testing') ? 'nullable' : 'required', new ValidTurnstile],
            'code' => [
                'required', 'string', 'max:64',
                // tenant-scoped existence; we'll re-check active window in controller
                Rule::exists('access_codes', 'code')->where(fn ($q) => $q->where('tenant_id', tenant('id'))
                ),
            ],
            'title' => ['nullable', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:8000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:4096'], // 4MB/file
            'category_id' => ['nullable', 'integer', Rule::exists('issue_categories', 'id')->where(fn ($q) => $q->where('tenant_id', tenant('id')))],
        ];
    }

    public function messages(): array
    {
        return [
            'code.exists'                   => 'Invalid access code.',
            'website.max'                   => 'Bot detected.',
            'cf-turnstile-response.required' => 'Please complete the security check.',
        ];
    }
}
