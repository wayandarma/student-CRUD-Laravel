@extends('layouts.app')

@section('content')
<div class="student-form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h1 class="form-card-title">
                <i class="bi bi-pencil-square form-card-title-icon" aria-hidden="true"></i>
                Edit Data Mahasiswa
            </h1>
            <p class="form-card-meta">ID #{{ $student->id }} &middot; Dibuat {{ $student->created_at->diffForHumans() }}</p>
        </div>

        <form
            id="student-delete-form"
            action="{{ route('students.destroy', $student) }}"
            method="POST"
            data-confirm
            data-confirm-title="Hapus mahasiswa?"
            data-confirm-text="Hapus data {{ $student->name }}? Tindakan ini tidak dapat dibatalkan."
            data-confirm-confirm-text="Ya, hapus"
            data-confirm-variant="danger"
        >
            @csrf
            @method('DELETE')
        </form>

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
                            <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Aktif</option>
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
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Kembali
                </a>

                <div class="student-form-actions">
                    <button type="submit" form="student-delete-form" class="btn-delete">
                        <i class="bi bi-trash3" aria-hidden="true"></i>
                        Hapus
                    </button>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        Perbarui Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
