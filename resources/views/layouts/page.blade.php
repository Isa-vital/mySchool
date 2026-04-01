<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') – mySchool UG</title>
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

        .logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            font-family: var(--font-h);
            letter-spacing: -1px;
        }

        .logo-text {
            font-family: var(--font-h);
            font-size: 18px;
            font-weight: 700;
        }

        .logo-text span {
            color: var(--green);
        }

        .logo-ug {
            font-size: 10px;
            background: var(--green-light);
            color: var(--green-dark);
            padding: 2px 8px;
            border-radius: 99px;
            font-weight: 700;
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
                <div class="logo-icon">mS</div>
                <div class="logo-text">my<span>School</span></div>
                <span class="logo-ug">UG</span>
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
        <div class="footer-copy">&copy; {{ date('Y') }} mySchool UG &middot; All rights reserved</div>
        <div class="footer-links">
            <a href="{{ route('pages.privacy') }}">Privacy Policy</a>
            <a href="{{ route('pages.terms') }}">Terms of Service</a>
            <a href="{{ route('pages.cookies') }}">Cookie Policy</a>
        </div>
    </footer>

</body>

</html>