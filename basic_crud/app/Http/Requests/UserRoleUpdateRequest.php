<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => 'required|in:student,admin',
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role wajib dipilih.',
            'role.in'       => 'Role yang dipilih tidak valid.',
        ];
    }
}
