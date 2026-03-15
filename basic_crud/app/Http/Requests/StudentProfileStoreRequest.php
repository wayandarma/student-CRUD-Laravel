<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentProfileStoreRequest extends FormRequest
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
            'name'            => 'required|string|max:255',
            'major'           => 'required|string|max:255',
            'enrollment_year' => 'required|integer|min:2000|max:' . now()->year,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Nama wajib diisi.',
            'name.max'                 => 'Nama maksimal :max karakter.',
            'major.required'           => 'Jurusan wajib diisi.',
            'major.max'                => 'Jurusan maksimal :max karakter.',
            'enrollment_year.required' => 'Tahun masuk wajib diisi.',
            'enrollment_year.integer'  => 'Tahun masuk harus berupa angka.',
            'enrollment_year.min'      => 'Tahun masuk minimal :min.',
            'enrollment_year.max'      => 'Tahun masuk tidak boleh melebihi tahun ini.',
        ];
    }
}
