@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-semibold mb-0">Detail Mahasiswa</h4>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-chip">
                            {{ strtoupper(substr($student->name, 0, 1)) }}{{ strtoupper(substr(strstr($student->name, ' ') ?: $student->name, 1, 1)) }}
                        </div>
                        <span class="fw-semibold">{{ $student->name }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        @can('delete', $student)
                        <form action="{{ route('students.destroy', $student) }}" method="POST"
                              data-confirm
                              data-confirm-title="Hapus Mahasiswa"
                              data-confirm-body="Yakin ingin menghapus data {{ $student->name }}?"
                              data-confirm-variant="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Nama</dt>
                        <dd class="col-sm-8">{{ $student->name }}</dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $student->email }}</dd>

                        <dt class="col-sm-4 text-muted">Jurusan</dt>
                        <dd class="col-sm-8">{{ $student->major }}</dd>

                        <dt class="col-sm-4 text-muted">Tahun Masuk</dt>
                        <dd class="col-sm-8 font-monospace">{{ $student->enrollment_year }}</dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </dd>

                        @if ($student->user)
                        <dt class="col-sm-4 text-muted">Akun Terhubung</dt>
                        <dd class="col-sm-8">
                            <span class="font-monospace">{{ $student->user->email }}</span>
                            <span class="badge bg-secondary ms-1">{{ $student->user->roleLabel() }}</span>
                        </dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Ditambahkan</dt>
                        <dd class="col-sm-8 text-muted small">{{ $student->created_at->diffForHumans() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
