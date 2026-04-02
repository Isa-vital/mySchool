<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') – uSchool</title>

    {{-- SEO --}}
    <meta name="description" content="@yield('meta_description', 'uSchool – Smarter school management for Ugandan primary and secondary schools.')" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="{{ url()->current() }}" />

    {{-- Open Graph --}}
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="@yield('title') – uSchool" />
    <meta property="og:description" content="@yield('meta_description', 'uSchool – Smarter school management for Ugandan primary and secondary schools.')" />
    <meta property="og:image" content="{{ asset('img/logo/uschoollogo.png') }}" />
    <meta property="og:site_name" content="uSchool" />

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon_io/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon_io/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon_io/favicon-16x16.png') }}" />
    <link rel="manifest" href="{{ asset('img/favicon_io/site.webmanifest') }}" />
    <meta name="theme-color" content="#1D9E75" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --green: #1D9E75;
            --green-dark: #0F6E56;
            --green-deeper: #085041;
            --green-light: #E1F5EE;
            --green-mid: #9FE1CB;
            --bg: #fff;
            --bg-dark: #0a1a14;
            --border: rgba(0, 0, 0, .07);
            --border-md: rgba(0, 0, 0, .13);
            --text: #111a15;
            --text-muted: #52635a;
            --text-hint: #9aaa9f;
            --font-h: 'Sora', sans-serif;
            --font-b: 'DM Sans', sans-serif;
            --r-sm: 8px;
            --r-md: 14px;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-b);
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* HEADER */
        header {
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-img {
            height: 38px;
            width: auto;
            object-fit: contain;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: var(--r-sm);
            border: 1px solid var(--border-md);
            background: transparent;
            cursor: pointer;
            transition: all .2s;
            font-family: var(--font-b);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover {
            border-color: var(--green);
            color: var(--green);
        }

        /* CONTENT */
        .page-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 2rem 5rem;
        }

        .page-content h1 {
            font-family: var(--font-h);
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -1px;
            margin-bottom: .5rem;
        }

        .page-meta {
            font-size: 13px;
            color: var(--text-hint);
            margin-bottom: 2.5rem;
        }

        .page-content h2 {
            font-family: var(--font-h);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin: 2rem 0 .75rem;
        }

        .page-content p {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.85;
            margin-bottom: 1rem;
        }

        .page-content ul {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .page-content ul li {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.85;
            margin-bottom: .35rem;
        }

        .page-content strong {
            color: var(--text);
            font-weight: 600;
        }

        /* FOOTER */
        footer {
            background: var(--bg-dark);
            border-top: 1px solid rgba(255, 255, 255, .06);
            padding: 2rem;
            text-align: center;
        }

        .footer-copy {
            font-size: 12px;
            color: rgba(255, 255, 255, .22);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: .75rem;
        }

        .footer-links a {
            font-size: 12px;
            color: rgba(255, 255, 255, .28);
            transition: color .2s;
        }

        .footer-links a:hover {
            color: rgba(255, 255, 255, .6);
        }
    </style>
</head>

<body>

    <header>
        <div class="nav-inner">
            <a href="/" class="logo">
                <img src="{{ asset('img/logo/uschoollogo.png') }}" alt="uSchool" class="logo-img" />
            </a>
            <div class="nav-right">
                <a href="/" class="btn-back">&larr; Back to Home</a>
            </div>
        </div>
    </header>

    <div class="page-content">
        @yield('content')
    </div>

    <footer>
        <div class="footer-copy">&copy; {{ date('Y') }} uSchool &middot; All rights reserved</div>
        <div class="footer-links">
            <a href="{{ route('pages.privacy') }}">Privacy Policy</a>
            <a href="{{ route('pages.terms') }}">Terms of Service</a>
            <a href="{{ route('pages.cookies') }}">Cookie Policy</a>
        </div>
    </footer>

</body>

</html>