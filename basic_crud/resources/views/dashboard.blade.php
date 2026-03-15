@extends('layouts.app')

@section('content')
    <div class="student-index-page">
        <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-mortarboard page-title-icon" aria-hidden="true"></i>
                    Student Management
                </h1>
                <p class="page-subtitle">Kelola data mahasiswa - {{ now()->format('l, d F Y') }}</p>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card kpi-card--blue">
                <div class="kpi-label">Total Mahasiswa</div>
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-meta">Semua data terdaftar</div>
                <div class="kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></div>
            </div>

            <div class="kpi-card kpi-card--green">
                <div class="kpi-label">Aktif</div>
                <div class="kpi-value">{{ $stats['active'] }}</div>
                <div class="kpi-meta">Status aktif saat ini</div>
                <div class="kpi-icon"><i class="bi bi-check-circle-fill" aria-hidden="true"></i></div>
            </div>

            <div class="kpi-card kpi-card--red">
                <div class="kpi-label">Tidak Aktif</div>
                <div class="kpi-value">{{ $stats['inactive'] }}</div>
                <div class="kpi-meta">Status tidak aktif</div>
                <div class="kpi-icon"><i class="bi bi-x-circle-fill" aria-hidden="true"></i></div>
            </div>

            <div class="kpi-card kpi-card--violet">
                <div class="kpi-label">Jurusan</div>
                <div class="kpi-value">{{ $stats['majors'] }}</div>
                <div class="kpi-meta">Total jurusan berbeda</div>
                <div class="kpi-icon"><i class="bi bi-building-fill" aria-hidden="true"></i></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <h2 class="table-card-title">
                    Daftar Mahasiswa
                    <span class="record-count">{{ $students->total() }} data</span>
                </h2>
            </div>

            <div class="table-toolbar">
                <form method="GET" action="{{ route('students.index') }}" class="table-toolbar-form" id="filterForm">
                    <div class="search-wrap">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="text" name="search" placeholder="Cari nama atau email..."
                            value="{{ request('search') }}" autocomplete="off" data-debounce-submit="420">
                    </div>

                    <select name="status" class="filter-select" data-auto-submit>
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>

                    <select name="major" class="filter-select" data-auto-submit>
                        <option value="">Semua Jurusan</option>
                        @foreach ($majors as $major)
                            <option value="{{ $major }}" {{ request('major') === $major ? 'selected' : '' }}>
                                {{ $major }}
                            </option>
                        @endforeach
                    </select>

                    @if (request()->hasAny(['search', 'status', 'major']))
                        <a href="{{ route('students.index') }}" class="btn-action table-toolbar-clear" title="Hapus filter"
                            aria-label="Hapus filter">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </a>
                    @endif

                    <a href="{{ route('students.create') }}" class="btn-add-student">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Tambah Mahasiswa
                    </a>
                </form>
            </div>

            <div class="table-scroll">
                <table class="data-table" aria-label="Daftar Mahasiswa">
                    <thead>
                        <tr>
                            <th class="table-column-index">#</th>
                            <th>Mahasiswa</th>
                            <th>Jurusan</th>
                            <th>Angkatan</th>
                            <th>Status</th>
                            <th class="table-column-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td class="table-cell-index">{{ $students->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="student-name-cell">
                                        <div class="student-avatar">{{ strtoupper(substr($student->name, 0, 2)) }}</div>
                                        <div>
                                            <div class="student-name">{{ $student->name }}</div>
                                            <div class="student-email">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="major-badge">{{ $student->major ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="year-badge">{{ $student->enrollment_year ?? '-' }}</span>
                                </td>
                                <td>
                                    @if ($student->status === 'active')
                                        <span class="status-badge status-badge--active">Aktif</span>
                                    @elseif ($student->status === 'inactive')
                                        <span class="status-badge status-badge--inactive">Tidak Aktif</span>
                                    @else
                                        <span
                                            class="status-badge status-badge--neutral">{{ ucfirst($student->status ?? '-') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-cell action-cell--center">
                                        <a href="{{ route('students.edit', $student) }}" class="btn-action" title="Edit"
                                            aria-label="Edit {{ $student->name }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>

                                        @can('delete', $student)
                                        <form action="{{ route('students.destroy', $student) }}" method="POST"
                                              data-confirm
                                              data-confirm-title="Hapus Mahasiswa"
                                              data-confirm-body="Yakin ingin menghapus data {{ $student->name }}? Tindakan ini tidak dapat dibatalkan."
                                              data-confirm-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-inbox" aria-hidden="true"></i></div>
                                        <div class="empty-state-title">
                                            @if (request()->hasAny(['search', 'status', 'major']))
                                                Tidak ada hasil yang cocok
                                            @else
                                                Belum ada data mahasiswa
                                            @endif
                                        </div>
                                        <p class="empty-state-text">
                                            @if (request()->hasAny(['search', 'status', 'major']))
                                                Coba ubah filter atau kata kunci pencarian Anda.
                                            @else
                                                Tambahkan data mahasiswa pertama Anda untuk memulai.
                                            @endif
                                        </p>
                                        @if (!request()->hasAny(['search', 'status', 'major']))
                                            <a href="{{ route('students.create') }}"
                                                class="btn-add-student btn-add-student--inline">
                                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                Tambah Mahasiswa
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages() || $students->total() > 0)
                <div class="table-footer">
                    <span class="record-info">
                        Menampilkan {{ $students->firstItem() }}-{{ $students->lastItem() }}
                        dari <strong>{{ $students->total() }}</strong> mahasiswa
                    </span>
                    @if ($students->hasPages())
                        <div class="pagination-wrap">
                            {{ $students->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection