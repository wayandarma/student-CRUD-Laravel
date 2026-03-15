@extends('layouts.app')

@section('title', $hasProfile ? 'Edit Profil' : 'Buat Profil')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">
                        {{ $hasProfile ? 'Edit Profil Akademik' : 'Buat Profil Akademik' }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($hasProfile)
                        <form action="{{ route('profile.update') }}" method="POST">
                            @method('PUT')
                    @else
                        <form action="{{ route('profile.store') }}" method="POST">
                    @endif
                        @csrf

                        {{-- Email: read-only, not submitted --}}
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <input type="text" class="form-control bg-light" value="{{ auth()->user()->email }}" disabled>
                            <div class="form-text">Email tidak dapat diubah.</div>
                        </div>

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $student?->name) }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Major --}}
                        <div class="mb-3">
                            <label for="major" class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="major"
                                name="major"
                                class="form-control @error('major') is-invalid @enderror"
                                value="{{ old('major', $student?->major) }}"
                                required
                            >
                            @error('major')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Enrollment Year --}}
                        <div class="mb-3">
                            <label for="enrollment_year" class="form-label">Tahun Masuk <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                id="enrollment_year"
                                name="enrollment_year"
                                class="form-control font-monospace @error('enrollment_year') is-invalid @enderror"
                                value="{{ old('enrollment_year', $student?->enrollment_year) }}"
                                min="2000"
                                max="{{ now()->year }}"
                                required
                            >
                            @error('enrollment_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($hasProfile)
                            <div class="mb-3">
                                <label class="form-label text-muted">Status Akademik</label>
                                <div>
                                    <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                    <small class="text-muted ms-2">Diatur oleh admin.</small>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            @if ($hasProfile)
                                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            @else
                                <button type="submit" class="btn btn-primary">Buat Profil</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
