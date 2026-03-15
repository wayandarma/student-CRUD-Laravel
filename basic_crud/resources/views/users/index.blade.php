@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-semibold mb-0">Manajemen Pengguna</h4>
        <span class="badge bg-secondary">{{ $users->count() }} pengguna</span>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Login Terakhir</th>
                        <th class="text-end">Ubah Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-chip">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: $user->name, 1, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-medium">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-muted small">(Anda)</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleClass = match($user->role) {
                                    'super_admin' => 'bg-primary',
                                    'admin'       => 'bg-warning text-dark',
                                    default       => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $roleClass }}">{{ $user->roleLabel() }}</span>
                        </td>
                        <td class="text-muted small font-monospace">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah login' }}
                        </td>
                        <td class="text-end">
                            @if ($user->id !== auth()->id() && !$user->isSuperAdmin())
                                <form action="{{ route('users.update-role', $user) }}" method="POST" class="d-inline-flex gap-2 align-items-center">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="form-select form-select-sm" style="width:auto;">
                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="admin"   {{ $user->role === 'admin'   ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
