@extends('layouts.app')

@section('content')

<style>
    .form-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        overflow: hidden;
        max-width: 640px;
        margin: 0 auto;
    }

    .form-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #F1F5F9;
        background: #FAFAFA;
    }

    .form-card-title {
        font-family: 'Fira Code', monospace;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #0F172A;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-card-meta {
        font-size: 0.75rem;
        color: #94A3B8;
        margin-top: 0.25rem;
    }

    .form-card-body {
        padding: 1.75rem 1.5rem;
    }

    .form-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.375rem;
    }

    .form-label .required {
        color: #DC2626;
        margin-left: 2px;
    }

    .form-control, .form-select {
        font-size: 0.875rem;
        border: 1px solid #D1D5DB;
        border-radius: 7px;
        padding: 0.5rem 0.75rem;
        color: #1E293B;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #DC2626;
    }

    .invalid-feedback {
        font-size: 0.75rem;
        color: #DC2626;
        margin-top: 0.25rem;
    }

    .form-card-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #F1F5F9;
        background: #FAFAFA;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-submit {
        background: #2563EB;
        color: white;
        border: none;
        border-radius: 7px;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        cursor: pointer;
        transition: background 150ms ease;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .btn-submit:hover { background: #1D4ED8; }

    .btn-cancel {
        color: #64748B;
        font-size: 0.875rem;
        text-decoration: none;
        padding: 0.5rem 0.75rem;
        border-radius: 7px;
        border: 1px solid #E2E8F0;
        transition: all 150ms ease;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .btn-cancel:hover { background: #F1F5F9; color: #1E293B; }

    .btn-delete {
        color: #DC2626;
        font-size: 0.8125rem;
        background: transparent;
        border: 1px solid #FECACA;
        border-radius: 7px;
        padding: 0.4rem 0.875rem;
        cursor: pointer;
        transition: all 150ms ease;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .btn-delete:hover { background: #FEE2E2; border-color: #FCA5A5; }
</style>

<div class="form-card">
    <div class="form-card-header">
        <h1 class="form-card-title">
            <i class="bi bi-pencil-square" style="color:#2563EB;"></i>
            Edit Data Mahasiswa
        </h1>
        <p class="form-card-meta">ID #{{ $student->id }} &middot; Dibuat {{ $student->created_at->diffForHumans() }}</p>
    </div>

    <form action="{{ route('students.update', $student) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="form-card-body">
            <div class="row g-3">

                <div class="col-12">
                    <label for="name" class="form-label">
                        Nama Lengkap <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $student->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="email" class="form-label">
                        Email <span class="required">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $student->email) }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="mahasiswa@example.com"
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="major" class="form-label">
                        Jurusan <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="major"
                        name="major"
                        value="{{ old('major', $student->major) }}"
                        class="form-control @error('major') is-invalid @enderror"
                        placeholder="cth. Teknik Informatika"
                    >
                    @error('major')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="enrollment_year" class="form-label">
                        Tahun Masuk <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="enrollment_year"
                        name="enrollment_year"
                        value="{{ old('enrollment_year', $student->enrollment_year) }}"
                        class="form-control @error('enrollment_year') is-invalid @enderror"
                        min="2000"
                        max="{{ now()->year }}"
                    >
                    @error('enrollment_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="status" class="form-label">
                        Status <span class="required">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="form-select @error('status') is-invalid @enderror"
                    >
                        <option value="active"   {{ old('status', $student->status) === 'active'   ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        <div class="form-card-footer">
            <a href="{{ route('students.index') }}" class="btn-cancel">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>

            <div class="d-flex gap-2">
                {{-- Delete --}}
                <form action="{{ route('students.destroy', $student) }}" method="POST"
                    onsubmit="return confirm('Hapus data {{ addslashes($student->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">
                        <i class="bi bi-trash3"></i> Hapus
                    </button>
                </form>

                {{-- Update --}}
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i> Perbarui Data
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
