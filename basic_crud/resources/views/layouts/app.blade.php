<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel App') }}</title>

    <!-- Google Fonts: Fira Code + Fira Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-primary:         #2563EB;
            --bs-primary-rgb:     37, 99, 235;
            --bs-body-font-family: 'Fira Sans', sans-serif;
            --bs-body-bg:         #F1F5F9;
            --bs-body-color:      #1E293B;

            --nav-bg:    #0F172A;
            --nav-border: #1E293B;
            --accent:    #2563EB;
            --surface:   #FFFFFF;
            --muted:     #64748B;
        }

        body {
            font-family: 'Fira Sans', sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Navbar ── */
        .app-navbar {
            background-color: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            padding: 0;
        }

        .app-navbar .navbar-brand {
            font-family: 'Fira Code', monospace;
            font-weight: 600;
            font-size: 1rem;
            color: #F8FAFC;
            letter-spacing: -0.02em;
            padding: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .app-navbar .navbar-brand .brand-dot {
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .app-navbar .nav-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
        }

        .app-navbar .user-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px;
            padding: 0.3rem 0.75rem;
            color: #CBD5E1;
            font-size: 0.813rem;
        }

        .app-navbar .user-chip .user-avatar {
            width: 22px;
            height: 22px;
            background: var(--accent);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fira Code', monospace;
            font-size: 0.7rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .btn-nav-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.18);
            color: #94A3B8;
            font-size: 0.813rem;
            padding: 0.3rem 0.875rem;
            border-radius: 6px;
            transition: all 150ms ease;
            cursor: pointer;
        }

        .btn-nav-logout:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.3);
            color: #E2E8F0;
        }

        .btn-nav-login {
            border: 1px solid rgba(255,255,255,0.18);
            color: #94A3B8;
            font-size: 0.813rem;
            padding: 0.3rem 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 150ms ease;
        }
        .btn-nav-login:hover { background: rgba(255,255,255,0.08); color: #E2E8F0; }

        .btn-nav-register {
            background: var(--accent);
            color: white;
            font-size: 0.813rem;
            font-weight: 500;
            padding: 0.3rem 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 150ms ease;
        }
        .btn-nav-register:hover { background: #1D4ED8; color: white; }

        /* ── Main content ── */
        main {
            padding: 2rem 0 3rem;
        }

        /* ── Alerts ── */
        .alert {
            font-size: 0.875rem;
            border-radius: 8px;
            border: none;
        }

        .alert-success {
            background: #DCFCE7;
            color: #14532D;
            border-left: 3px solid #16A34A;
        }

        .alert-danger {
            background: #FEE2E2;
            color: #7F1D1D;
            border-left: 3px solid #DC2626;
        }

        /* ── Bootstrap overrides ── */
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #1D4ED8;
            border-color: #1D4ED8;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
    </style>
</head>

<body>
    <nav class="app-navbar navbar">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <span class="brand-dot"></span>
                StudentMS
            </a>

            <div class="nav-meta">
                @auth
                    <div class="user-chip">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        {{ auth()->user()->name }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn-nav-logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
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
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>
