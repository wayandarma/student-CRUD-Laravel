<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'StudentMS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $flashMessages = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'info', 'message' => session('info')],
    ])->filter(fn (array $flash): bool => filled($flash['message']))->values();
@endphp

<body>
    <script id="app-flash-data" type="application/json">@json($flashMessages)</script>

    <nav class="app-navbar navbar">
        <div class="container">
            <a class="navbar-brand" href="{{ auth()->check() ? route('dashboard') : url('/') }}">
                <span class="brand-dot"></span>
                StudentMS
            </a>

            <div class="nav-meta">
                @auth
                    <div class="nav-quick-links">
                        @if (auth()->user()->isAdminLevel())
                            <a href="{{ route('students.index') }}"
                                class="nav-quick-link {{ request()->routeIs('students.*') ? 'is-active' : '' }}">
                                Data Mahasiswa
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}"
                                class="nav-quick-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                                Portal Saya
                            </a>
                        @endif
                    </div>

                    <div class="user-chip">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="user-chip__content">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="role-pill role-pill--{{ auth()->user()->role }}">
                                {{ auth()->user()->roleLabel() }}
                            </span>
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn-nav-logout">
                            <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">Login</a>
                    <a href="{{ route('register') }}" class="btn-nav-register">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>
