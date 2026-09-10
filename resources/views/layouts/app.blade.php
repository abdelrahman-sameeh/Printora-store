<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
        :root {
            --brand: #5b4cf0;
            --brand-dark: #4033c7;
            --ink: #182230;
            --muted: #667085;
        }

        body {
            min-height: 100vh;
            color: var(--ink);
            font-family: "Cairo", sans-serif;
            background:
                radial-gradient(circle at 10% 10%, rgba(91, 76, 240, .14), transparent 32rem),
                radial-gradient(circle at 90% 90%, rgba(15, 188, 150, .12), transparent 30rem),
                #f8f9fc;
        }

        .navbar-brand {
            color: var(--ink);
            font-weight: 800;
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

        .btn-brand {
            display: inline-flex;
            min-height: 3.15rem;
            color: #fff;
            font-weight: 700;
            border: 0;
            border-radius: .85rem;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            box-shadow: 0 .75rem 1.5rem rgba(91, 76, 240, .22);
        }

        .btn-brand:hover,
        .btn-brand:focus {
            color: #fff;
            transform: translateY(-1px);
            background: linear-gradient(135deg, #5142e5, #3529b2);
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
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <span class="brand-mark">P</span>
                <span>Printora</span>
            </a>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>