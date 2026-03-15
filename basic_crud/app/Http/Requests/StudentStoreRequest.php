<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:students,email',
            'major'           => 'required|string|max:255',
            'status'          => 'required|in:active,inactive',
            'enrollment_year' => 'required|integer|min:2000|max:' . now()->year,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Nama mahasiswa wajib diisi.',
            'name.max'                 => 'Nama maksimal :max karakter.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email ini sudah terdaftar.',
            'major.required'           => 'Jurusan wajib diisi.',
            'major.max'                => 'Jurusan maksimal :max karakter.',
            'status.required'          => 'Status wajib dipilih.',
            'status.in'                => 'Status hanya boleh aktif atau tidak aktif.',
            'enrollment_year.required' => 'Tahun masuk wajib diisi.',
            'enrollment_year.integer'  => 'Tahun masuk harus berupa angka.',
            'enrollment_year.min'      => 'Tahun masuk minimal :min.',
            'enrollment_year.max'      => 'Tahun masuk tidak boleh melebihi tahun ini.',
        ];
    }
}
