@extends('layouts.app')

@section('content')

<style>
    /* ── Page header ── */
    .page-header {
        margin-bottom: 2rem;
    }

    .page-header .page-title {
        font-family: 'Fira Code', monospace;
        font-size: 1.375rem;
        font-weight: 600;
        color: #0F172A;
        letter-spacing: -0.03em;
        margin: 0;
    }

    .page-header .page-subtitle {
        font-size: 0.875rem;
        color: #64748B;
        margin: 0.25rem 0 0;
    }

    /* ── KPI Cards ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 991px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 575px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    }

    .kpi-card {
        background: #FFFFFF;
        border-radius: 10px;
        padding: 1.25rem 1.375rem;
        border: 1px solid #E2E8F0;
        position: relative;
        overflow: hidden;
        transition: box-shadow 180ms ease, transform 180ms ease;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--kpi-accent, #2563EB);
        border-radius: 3px 0 0 3px;
    }

    .kpi-card:hover {
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
        transform: translateY(-1px);
    }

    .kpi-card .kpi-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
    }

    .kpi-card .kpi-value {
        font-family: 'Fira Code', monospace;
        font-size: 2rem;
        font-weight: 700;
        color: #0F172A;
        line-height: 1;
        margin-bottom: 0.375rem;
    }

    .kpi-card .kpi-meta {
        font-size: 0.75rem;
        color: #94A3B8;
    }

    .kpi-card .kpi-icon {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: var(--kpi-icon-bg, #EFF6FF);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        color: var(--kpi-accent, #2563EB);
    }

    .kpi-card--blue   { --kpi-accent: #2563EB; --kpi-icon-bg: #EFF6FF; }
    .kpi-card--green  { --kpi-accent: #16A34A; --kpi-icon-bg: #DCFCE7; }
    .kpi-card--red    { --kpi-accent: #DC2626; --kpi-icon-bg: #FEE2E2; }
    .kpi-card--violet { --kpi-accent: #7C3AED; --kpi-icon-bg: #EDE9FE; }

    /* ── Table card ── */
    .table-card {
        background: #FFFFFF;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        overflow: hidden;
    }

    .table-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .table-card-title {
        font-family: 'Fira Code', monospace;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #0F172A;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-card-title .record-count {
        font-family: 'Fira Code', monospace;
        font-size: 0.75rem;
        font-weight: 500;
        color: #64748B;
        background: #F1F5F9;
        border-radius: 4px;
        padding: 0.1rem 0.5rem;
    }

    /* ── Toolbar (search + filter) ── */
    .table-toolbar {
        padding: 0.875rem 1.5rem;
        background: #FAFAFA;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }

    .search-wrap .bi {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 0.875rem;
        pointer-events: none;
    }

    .search-wrap input {
        padding-left: 2.25rem;
        font-size: 0.875rem;
        border: 1px solid #E2E8F0;
        border-radius: 7px;
        height: 36px;
        width: 100%;
        outline: none;
        background: #fff;
        color: #1E293B;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .search-wrap input:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .filter-select {
        font-size: 0.875rem;
        border: 1px solid #E2E8F0;
        border-radius: 7px;
        height: 36px;
        padding: 0 0.75rem;
        outline: none;
        background: #fff;
        color: #1E293B;
        cursor: pointer;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .filter-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-add-student {
        background: #2563EB;
        color: white;
        border: none;
        border-radius: 7px;
        font-size: 0.8125rem;
        font-weight: 500;
        height: 36px;
        padding: 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        cursor: pointer;
        text-decoration: none;
        transition: background 150ms ease;
        white-space: nowrap;
        margin-left: auto;
    }

    .btn-add-student:hover {
        background: #1D4ED8;
        color: white;
    }

    /* ── Data table ── */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .data-table thead tr {
        border-bottom: 2px solid #F1F5F9;
    }

    .data-table thead th {
        padding: 0.875rem 1.25rem;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        white-space: nowrap;
        background: #FAFAFA;
        border-bottom: 1px solid #E2E8F0;
    }

    .data-table tbody tr {
        border-bottom: 1px solid #F8FAFC;
        transition: background 120ms ease;
        cursor: default;
    }

    .data-table tbody tr:hover {
        background: #F8FAFC;
    }

    .data-table tbody tr:last-child {
        border-bottom: none;
    }

    .data-table td {
        padding: 1rem 1.25rem;
        color: #1E293B;
        vertical-align: middle;
    }

    /* Student name cell */
    .student-name-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #EFF6FF;
        color: #2563EB;
        font-family: 'Fira Code', monospace;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .student-name {
        font-weight: 500;
        color: #0F172A;
        line-height: 1.3;
    }

    .student-email {
        font-size: 0.75rem;
        color: #94A3B8;
        margin-top: 1px;
    }

    /* Major badge */
    .major-badge {
        display: inline-flex;
        align-items: center;
        background: #F1F5F9;
        color: #475569;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.2rem 0.6rem;
        white-space: nowrap;
    }

    /* Year badge */
    .year-badge {
        font-family: 'Fira Code', monospace;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 5px;
        padding: 0.2rem 0.5rem;
        display: inline-block;
    }

    /* Status badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.625rem;
        border-radius: 99px;
        white-space: nowrap;
    }

    .status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-badge--active {
        background: #DCFCE7;
        color: #15803D;
    }
    .status-badge--active::before { background: #16A34A; }

    .status-badge--inactive {
        background: #FEE2E2;
        color: #B91C1C;
    }
    .status-badge--inactive::before { background: #DC2626; }

    /* Action buttons */
    .action-cell {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .btn-action {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        border: 1px solid #E2E8F0;
        background: transparent;
        color: #64748B;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 150ms ease;
        text-decoration: none;
    }

    .btn-action:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
        color: #1E293B;
    }

    .btn-action--danger:hover {
        background: #FEE2E2;
        border-color: #FECACA;
        color: #DC2626;
    }

    /* ── Empty state ── */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        background: #F1F5F9;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #94A3B8;
        margin: 0 auto 1.25rem;
    }

    .empty-state-title {
        font-weight: 600;
        color: #1E293B;
        margin-bottom: 0.375rem;
        font-size: 0.9375rem;
    }

    .empty-state-text {
        font-size: 0.875rem;
        color: #64748B;
        margin-bottom: 1.25rem;
    }

    /* ── Table footer / pagination ── */
    .table-footer {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #FAFAFA;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .table-footer .record-info {
        font-size: 0.8125rem;
        color: #64748B;
    }

    .pagination-wrap .page-link {
        font-size: 0.8125rem;
        border-color: #E2E8F0;
        color: #475569;
        padding: 0.3rem 0.65rem;
    }

    .pagination-wrap .page-item.active .page-link {
        background: #2563EB;
        border-color: #2563EB;
        color: white;
    }

    /* ── Overflow table wrapper ── */
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>

{{-- ── Page Header ── --}}
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title">
            <i class="bi bi-mortarboard me-2" style="color:#2563EB;font-style:normal;"></i>Student Management
        </h1>
        <p class="page-subtitle">Kelola data mahasiswa — {{ now()->format('l, d F Y') }}</p>
    </div>
</div>

{{-- ── KPI Summary Cards ── --}}
<div class="kpi-grid">
    <div class="kpi-card kpi-card--blue">
        <div class="kpi-label">Total Mahasiswa</div>
        <div class="kpi-value">{{ isset($students) ? $students->total() : '—' }}</div>
        <div class="kpi-meta">Semua data terdaftar</div>
        <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
    </div>

    <div class="kpi-card kpi-card--green">
        <div class="kpi-label">Aktif</div>
        <div class="kpi-value">{{ isset($students) ? $students->getCollection()->where('status', 'active')->count() : '—' }}</div>
        <div class="kpi-meta">Status aktif saat ini</div>
        <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
    </div>

    <div class="kpi-card kpi-card--red">
        <div class="kpi-label">Tidak Aktif</div>
        <div class="kpi-value">{{ isset($students) ? $students->getCollection()->where('status', 'inactive')->count() : '—' }}</div>
        <div class="kpi-meta">Status tidak aktif</div>
        <div class="kpi-icon"><i class="bi bi-x-circle-fill"></i></div>
    </div>

    <div class="kpi-card kpi-card--violet">
        <div class="kpi-label">Jurusan</div>
        <div class="kpi-value">{{ isset($students) ? $students->getCollection()->pluck('major')->unique()->count() : '—' }}</div>
        <div class="kpi-meta">Total jurusan berbeda</div>
        <div class="kpi-icon"><i class="bi bi-building-fill"></i></div>
    </div>
</div>

{{-- ── Student Table Card ── --}}
<div class="table-card">

    {{-- Header --}}
    <div class="table-card-header">
        <h2 class="table-card-title">
            Daftar Mahasiswa
            @isset($students)
                <span class="record-count">{{ $students->total() }} data</span>
            @endisset
        </h2>
    </div>

    {{-- Toolbar --}}
    <div class="table-toolbar">
        <form method="GET" action="{{ route('students.index') }}" class="d-flex align-items-center gap-2 flex-wrap w-100" id="filterForm">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama atau email..."
                    value="{{ request('search') }}"
                    autocomplete="off"
                    oninput="debounceSubmit()"
                >
            </div>

            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>

            <select name="major" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Jurusan</option>
                @isset($majors)
                    @foreach ($majors as $major)
                        <option value="{{ $major }}" {{ request('major') === $major ? 'selected' : '' }}>
                            {{ $major }}
                        </option>
                    @endforeach
                @endisset
            </select>

            @if (request()->hasAny(['search', 'status', 'major']))
                <a href="{{ route('students.index') }}" class="btn-action" title="Hapus filter" style="text-decoration:none;">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif

            <a href="{{ route('students.create') }}" class="btn-add-student">
                <i class="bi bi-plus-lg"></i> Tambah Mahasiswa
            </a>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-scroll">
        <table class="data-table" aria-label="Daftar Mahasiswa">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Mahasiswa</th>
                    <th>Jurusan</th>
                    <th>Angkatan</th>
                    <th>Status</th>
                    <th style="width:90px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="studentTableBody">
                @isset($students)
                    @forelse ($students as $index => $student)
                        <tr>
                            <td style="color:#94A3B8; font-family:'Fira Code',monospace; font-size:0.75rem;">
                                {{ $students->firstItem() + $loop->index }}
                            </td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">
                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="student-name">{{ $student->name }}</div>
                                        <div class="student-email">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="major-badge">{{ $student->major ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="year-badge">{{ $student->enrollment_year ?? '—' }}</span>
                            </td>
                            <td>
                                @if ($student->status === 'active')
                                    <span class="status-badge status-badge--active">Aktif</span>
                                @elseif ($student->status === 'inactive')
                                    <span class="status-badge status-badge--inactive">Tidak Aktif</span>
                                @else
                                    <span class="status-badge" style="background:#F1F5F9;color:#64748B;">{{ ucfirst($student->status ?? '—') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-cell" style="justify-content:center;">
                                    <a href="{{ route('students.edit', $student) }}" class="btn-action" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('students.destroy', $student) }}" method="POST"
                                        onsubmit="return confirm('Hapus data {{ addslashes($student->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action--danger" title="Hapus">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
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
                                        <a href="{{ route('students.create') }}" class="btn-add-student d-inline-flex">
                                            <i class="bi bi-plus-lg"></i> Tambah Mahasiswa
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                @else
                    {{-- Placeholder rows when $students is not passed yet --}}
                    @for ($i = 1; $i <= 5; $i++)
                        <tr style="opacity: {{ 1 - ($i - 1) * 0.15 }}">
                            <td style="color:#94A3B8; font-family:'Fira Code',monospace; font-size:0.75rem;">{{ $i }}</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar" style="background:#F1F5F9; color:#CBD5E1;">??</div>
                                    <div>
                                        <div class="student-name" style="color:#CBD5E1; background:#F1F5F9; border-radius:4px; width:140px; height:14px;"></div>
                                        <div class="student-email" style="background:#F8FAFC; border-radius:4px; width:100px; height:10px; margin-top:5px;"></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="major-badge" style="background:#F8FAFC; color:#F8FAFC; width:80px; height:20px; display:inline-block;"></span></td>
                            <td><span class="year-badge" style="background:#F8FAFC; color:#F8FAFC; width:40px; display:inline-block;"></span></td>
                            <td><span class="status-badge" style="background:#F8FAFC; width:60px; height:20px;"></span></td>
                            <td>
                                <div class="action-cell" style="justify-content:center;">
                                    <span class="btn-action" style="background:#F8FAFC; border-color:#F8FAFC;"></span>
                                    <span class="btn-action" style="background:#F8FAFC; border-color:#F8FAFC;"></span>
                                    <span class="btn-action" style="background:#F8FAFC; border-color:#F8FAFC;"></span>
                                </div>
                            </td>
                        </tr>
                    @endfor
                    <tr>
                        <td colspan="6">
                            <div class="empty-state" style="padding: 1.5rem 2rem;">
                                <p class="empty-state-text" style="margin:0;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Hubungkan <code>$students</code> dari <strong>StudentController</strong> untuk menampilkan data nyata.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endisset
            </tbody>
        </table>
    </div>

    {{-- Pagination + record info --}}
    @isset($students)
        @if ($students->hasPages() || $students->total() > 0)
            <div class="table-footer">
                <span class="record-info">
                    Menampilkan {{ $students->firstItem() }}–{{ $students->lastItem() }}
                    dari <strong>{{ $students->total() }}</strong> mahasiswa
                </span>
                @if ($students->hasPages())
                    <div class="pagination-wrap">
                        {{ $students->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        @endif
    @endisset
</div>

<script>
    // Debounced search submit
    let searchTimer;
    function debounceSubmit() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 420);
    }

    // Delete confirmation
    function confirmDelete(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data mahasiswa ini?')) {
            // Dispatch to your delete route
            // fetch(`/students/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            alert('Hubungkan ke route delete: DELETE /students/' + id);
        }
    }
</script>
@endsection
