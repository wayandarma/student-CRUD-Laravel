@extends('layouts.app')

@section('content')
<div class="student-portal-page">
    <section class="portal-hero">
        <article class="portal-hero-card">
            <p class="portal-eyebrow">Portal Mahasiswa</p>
            <h1 class="portal-title">Halo, {{ auth()->user()->name }}</h1>
            <p class="portal-subtitle">
                Akun Anda saat ini menggunakan akses mahasiswa. Anda dapat melihat ringkasan profil yang terhubung,
                sementara pengelolaan data mahasiswa tetap dibatasi untuk admin.
            </p>
        </article>

        <aside class="portal-role-card">
            <div>
                <p class="portal-role-label">Status akun</p>
                <span class="role-pill role-pill--{{ auth()->user()->role }}">{{ auth()->user()->roleLabel() }}</span>
            </div>
            <p class="portal-role-email">{{ auth()->user()->email }}</p>
        </aside>
    </section>

    @if ($studentProfile)
        <section class="portal-grid">
            <article class="portal-card">
                <p class="portal-card-label">Nama</p>
                <h2 class="portal-card-title">{{ $studentProfile->name }}</h2>
                <p class="portal-card-meta">{{ $studentProfile->email }}</p>
            </article>

            <article class="portal-card">
                <p class="portal-card-label">Jurusan</p>
                <h2 class="portal-card-title">{{ $studentProfile->major }}</h2>
                <p class="portal-card-meta">Angkatan {{ $studentProfile->enrollment_year }}</p>
            </article>

            <article class="portal-card">
                <p class="portal-card-label">Status Akademik</p>
                <h2 class="portal-card-title">
                    <span class="portal-status portal-status--{{ $studentProfile->status }}">
                        {{ $studentProfile->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </h2>
                <p class="portal-card-meta">Profil mahasiswa ini sudah ditautkan ke akun Anda.</p>
            </article>
        </section>
    @else
        <section class="portal-empty">
            <h2 class="portal-empty-title">Profil mahasiswa belum terhubung</h2>
            <p class="portal-empty-text">
                Akun mahasiswa Anda sudah aktif, tetapi belum ada data profil mahasiswa yang dikaitkan. Hubungi admin
                agar akun ini dapat ditautkan ke data akademik Anda.
            </p>
        </section>
    @endif
</div>
@endsection
