@extends('layouts.app')

@section('content')
<div class="student-form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h1 class="form-card-title">
                <i class="bi bi-person-plus form-card-title-icon" aria-hidden="true"></i>
                Tambah Mahasiswa
            </h1>
        </div>

        <form action="{{ route('students.store') }}" method="POST" novalidate>
            @csrf
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
                            value="{{ old('name') }}"
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
                            value="{{ old('email') }}"
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
                            value="{{ old('major') }}"
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
                            value="{{ old('enrollment_year') }}"
                            class="form-control @error('enrollment_year') is-invalid @enderror"
                            placeholder="{{ now()->year }}"
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
                            <option value="" disabled {{ old('status') ? '' : 'selected' }}>Pilih status</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
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
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
