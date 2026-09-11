<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand: #5b4cf0;
            --brand-dark: #4033c7;
            --ink: #182230;
            --muted: #667085;
            --brand-soft: #eeecff;
            --danger: #d92d20;
            --danger-dark: #b42318;
            --danger-soft: #fee4e2;
            --neutral-soft: #f2f4f7;
        }

        body {
            min-height: 100vh;
            color: var(--ink);
            font-family: "Readex Pro Variable", sans-serif;
            background:
                radial-gradient(circle at 10% 10%, rgba(91, 76, 240, .14), transparent 32rem),
                radial-gradient(circle at 90% 90%, rgba(15, 188, 150, .12), transparent 30rem),
                #f8f9fc;
        }

        .navbar-brand {
            color: var(--ink);
            font-weight: 700;
            letter-spacing: -.04em;
        }

        .brand-mark {
            display: inline-grid;
            width: 2.25rem;
            height: 2.25rem;
            margin-left: .55rem;
            color: #fff;
            place-items: center;
            border-radius: .75rem;
            background: linear-gradient(135deg, var(--brand), #8a7fff);
            box-shadow: 0 .5rem 1.25rem rgba(91, 76, 240, .28);
        }

        .page-shell {
            min-height: calc(100vh - 73px);
        }

        .auth-card {
            overflow: hidden;
            border: 1px solid rgba(16, 24, 40, .07);
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 1.5rem 4rem rgba(16, 24, 40, .1);
            backdrop-filter: blur(12px);
        }

        .auth-aside {
            position: relative;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(145deg, #171331, var(--brand-dark));
        }

        .auth-aside::before,
        .auth-aside::after {
            position: absolute;
            content: "";
            border-radius: 50%;
            background: rgba(255, 255, 255, .09);
        }

        .auth-aside::before {
            width: 18rem;
            height: 18rem;
            top: -8rem;
            left: -7rem;
        }

        .auth-aside::after {
            width: 12rem;
            height: 12rem;
            right: -5rem;
            bottom: -5rem;
        }

        .form-control,
        .form-select {
            min-height: 3.15rem;
            border-color: #e4e7ec;
            border-radius: .85rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(91, 76, 240, .55);
            box-shadow: 0 0 0 .25rem rgba(91, 76, 240, .12);
        }

        .btn {
            display: inline-flex;
            min-height: 3.15rem;
            padding: .7rem 1rem;
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.25;
            border-radius: .85rem;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            box-shadow: 0 .35rem .85rem rgba(16, 24, 40, .08);
            transition: color .2s ease, background-color .2s ease, border-color .2s ease,
                box-shadow .2s ease, transform .2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 .55rem 1rem rgba(16, 24, 40, .12);
        }

        .btn:active {
            transform: translateY(0);
            box-shadow: 0 .2rem .5rem rgba(16, 24, 40, .08);
        }

        .btn:focus-visible {
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(91, 76, 240, .18);
        }

        .btn:disabled,
        .btn.disabled {
            transform: none;
            box-shadow: none;
        }

        .btn-brand,
        .btn-primary {
            color: #fff;
            border-color: var(--brand);
            background-color: var(--brand);
        }

        .btn-brand:hover,
        .btn-brand:focus,
        .btn-primary:hover,
        .btn-primary:focus {
            color: #fff;
            border-color: var(--brand-dark);
            background-color: var(--brand-dark);
        }

        .btn-outline-primary {
            color: var(--brand-dark);
            border-color: var(--brand-soft);
            background-color: var(--brand-soft);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: #fff;
            border-color: var(--brand);
            background-color: var(--brand);
        }

        .btn-danger {
            color: #fff;
            border-color: var(--danger);
            background-color: var(--danger);
        }

        .btn-danger:hover,
        .btn-danger:focus {
            color: #fff;
            border-color: var(--danger-dark);
            background-color: var(--danger-dark);
        }

        .btn-outline-danger {
            color: var(--danger-dark);
            border-color: var(--danger-soft);
            background-color: var(--danger-soft);
        }

        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            color: #fff;
            border-color: var(--danger);
            background-color: var(--danger);
        }

        .btn-light {
            color: var(--ink);
            border-color: #e4e7ec;
            background-color: var(--neutral-soft);
        }

        .btn-light:hover,
        .btn-light:focus {
            color: var(--ink);
            border-color: #d0d5dd;
            background-color: #e4e7ec;
        }

        .btn-outline-dark {
            color: #fff;
            border-color: var(--ink);
            background-color: var(--ink);
        }

        .btn-outline-dark:hover,
        .btn-outline-dark:focus {
            color: #fff;
            border-color: #101828;
            background-color: #101828;
        }

        .text-brand {
            color: var(--brand) !important;
        }

        .text-muted-custom {
            color: var(--muted);
        }

        .feature-icon {
            display: grid;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            place-items: center;
            border-radius: .8rem;
            background: rgba(255, 255, 255, .13);
        }

        .dashboard-card {
            border: 1px solid rgba(16, 24, 40, .07);
            border-radius: 1.25rem;
            box-shadow: 0 1rem 2.5rem rgba(16, 24, 40, .07);
        }
    </style>
    @stack('styles')
</head>

<body>
    <nav class="navbar navbar-expand bg-white border-bottom py-3">
        <div class="container">
            @auth
                <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                    <span class="brand-mark">
                        {{ mb_substr(auth()->user()->first_name, 0, 1) }}{{ mb_substr(auth()->user()->last_name, 0, 1) }}
                    </span>
                    <span>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                </a>
            @else
                <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                    <span class="brand-mark">G</span>
                    <span>Guest</span>
                </a>
            @endauth

            <div class="d-flex align-items-center gap-2">
                @auth
                    <a class="btn btn-light px-3" href="{{ route('dashboard') }}">حسابي</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-danger px-3" type="submit">تسجيل الخروج</button>
                    </form>
                @else
                    <a class="btn btn-light px-3" href="{{ route('login') }}">دخول</a>
                    <a class="btn btn-brand px-3" href="{{ route('register') }}">حساب جديد</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (session('success'))
        <div class="container pt-3">
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        </div>
    @endif

    <main class="page-shell">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
