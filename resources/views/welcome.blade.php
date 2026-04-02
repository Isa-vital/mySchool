<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>uSchool – Smarter School Management for Ugandan Schools</title>

    {{-- Primary SEO --}}
    <meta name="description" content="uSchool is the all-in-one school management platform built for Ugandan primary and secondary schools. Manage students, fees, attendance, grades, report cards, and parent communication — all from one dashboard." />
    <meta name="keywords" content="school management system Uganda, school management software, student management, fee management, attendance tracking, report cards Uganda, school ERP, uSchool, primary school management, secondary school management, Kampala school software" />
    <meta name="author" content="uSchool" />
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <link rel="canonical" href="{{ url('/') }}" />

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:title" content="uSchool – Smarter School Management for Ugandan Schools" />
    <meta property="og:description" content="Manage students, fees, attendance, grades, and parent communication in one simple platform. Built for Ugandan P1–S6 schools. Start your free 30-day trial." />
    <meta property="og:image" content="{{ asset('img/logo/uschoollogo.png') }}" />
    <meta property="og:image:alt" content="uSchool – Smarter School Management" />
    <meta property="og:site_name" content="uSchool" />
    <meta property="og:locale" content="en_UG" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="uSchool – Smarter School Management for Ugandan Schools" />
    <meta name="twitter:description" content="The all-in-one school management platform for Ugandan primary &amp; secondary schools. Students, fees, attendance, grades — all in one place." />
    <meta name="twitter:image" content="{{ asset('img/logo/uschoollogo.png') }}" />

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon_io/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon_io/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon_io/favicon-16x16.png') }}" />
    <link rel="manifest" href="{{ asset('img/favicon_io/site.webmanifest') }}" />
    <meta name="theme-color" content="#1D9E75" />

    {{-- Structured Data (JSON-LD) --}}
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "SoftwareApplication",
            "name": "uSchool",
            "url": "{{ url('/') }}",
            "description": "The all-in-one school management platform built for Ugandan primary and secondary schools. Manage students, fees, attendance, grades, and parent communication.",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "offers": {
                "@@type": "AggregateOffer",
                "priceCurrency": "UGX",
                "lowPrice": "750000",
                "highPrice": "1300000",
                "offerCount": "3"
            },
            "aggregateRating": {
                "@@type": "AggregateRating",
                "ratingValue": "4.9",
                "reviewCount": "120",
                "bestRating": "5"
            },
            "publisher": {
                "@@type": "Organization",
                "name": "uSchool",
                "url": "{{ url('/') }}",
                "logo": "{{ asset('img/logo/uschoollogo.png') }}",
                "contactPoint": {
                    "@@type": "ContactPoint",
                    "telephone": "+256-700-000-000",
                    "contactType": "customer service",
                    "areaServed": "UG",
                    "availableLanguage": "English"
                },
                "address": {
                    "@@type": "PostalAddress",
                    "addressLocality": "Kampala",
                    "addressCountry": "UG"
                }
            }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
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
            --blue: #378ADD;
            --blue-light: #E6F1FB;
            --amber: #EF9F27;
            --bg: #ffffff;
            --bg-off: #f7faf8;
            --bg-dark: #0a1a14;
            --bg-dark2: #0d1f17;
            --border: rgba(0, 0, 0, 0.07);
            --border-md: rgba(0, 0, 0, 0.13);
            --text: #111a15;
            --text-muted: #52635a;
            --text-hint: #9aaa9f;
            --font-h: 'Sora', sans-serif;
            --font-b: 'DM Sans', sans-serif;
            --r-sm: 8px;
            --r-md: 14px;
            --r-lg: 20px;
            --r-xl: 28px;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-b);
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
            overflow-x: hidden;
        }

        img {
            display: block;
            max-width: 100%;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 200;
            transition: background .3s, box-shadow .3s;
        }

        header.scrolled {
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 1px 0 var(--border), 0 4px 24px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(16px);
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

        .nav-links {
            display: flex;
            align-items: center;
            gap: .2rem;
        }

        .nav-links a {
            font-size: 14px;
            color: var(--text-muted);
            padding: 6px 13px;
            border-radius: var(--r-sm);
            transition: color .2s, background .2s;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--green-dark);
            background: var(--green-light);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-login {
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
        }

        .btn-login:hover {
            border-color: var(--green);
            color: var(--green);
        }

        .btn-header {
            font-size: 14px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: var(--r-sm);
            border: none;
            background: var(--green);
            color: #fff;
            cursor: pointer;
            font-family: var(--font-b);
            transition: background .2s, transform .15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-header:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        .hamburger {
            display: none;
            background: none;
            border: 1px solid var(--border-md);
            border-radius: 8px;
            padding: 8px;
            cursor: pointer;
            line-height: 1;
            color: var(--text);
            transition: border-color .2s, background .2s;
        }

        .hamburger:hover {
            border-color: var(--green);
            background: var(--green-light);
        }

        .hamburger svg {
            width: 22px;
            height: 22px;
            display: block;
        }

        /* MOBILE MENU */
        .mobile-menu {
            position: fixed;
            inset: 0;
            z-index: 300;
            visibility: hidden;
            pointer-events: none;
        }

        .mobile-menu.open {
            visibility: visible;
            pointer-events: auto;
        }

        .mobile-menu-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity .3s ease;
        }

        .mobile-menu.open .mobile-menu-overlay {
            opacity: 1;
        }

        .mobile-menu-panel {
            position: relative;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            transform: translateY(-100%);
            transition: transform .35s cubic-bezier(.4, 0, .2, 1);
            max-height: 100vh;
            overflow-y: auto;
        }

        .mobile-menu.open .mobile-menu-panel {
            transform: translateY(0);
        }

        .mobile-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .mobile-menu-close {
            background: none;
            border: 1px solid var(--border-md);
            border-radius: 8px;
            padding: 6px;
            cursor: pointer;
            color: var(--text-muted);
            line-height: 1;
            transition: border-color .2s, color .2s;
        }

        .mobile-menu-close:hover {
            border-color: var(--green);
            color: var(--green-dark);
        }

        .mobile-menu-close svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        .mobile-menu-links {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .mobile-menu-links a {
            display: block;
            padding: 12px 16px;
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            border-radius: var(--r-sm);
            transition: background .2s, color .2s;
        }

        .mobile-menu-links a:hover {
            background: var(--green-light);
            color: var(--green-dark);
        }

        .mobile-menu-actions {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mobile-menu-actions .btn-login {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 12px;
            font-size: 15px;
        }

        .mobile-menu-actions .btn-header {
            justify-content: center;
            width: 100%;
            padding: 12px 20px;
            font-size: 15px;
        }

        /* HERO */
        .hero {
            padding-top: 96px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
            background: linear-gradient(145deg, #e6f7f0 0%, #edf4fc 60%, #e6f7f0 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -200px;
            right: -150px;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(29, 158, 117, .13) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-left {
            padding: 4rem 3rem 4rem 5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid var(--green-mid);
            border-radius: 99px;
            padding: 5px 16px 5px 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--green-dark);
            margin-bottom: 1.75rem;
            width: fit-content;
        }

        .pill-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .hero h1 {
            font-family: var(--font-h);
            font-size: clamp(2.4rem, 3.8vw, 3.4rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            color: var(--text);
            margin-bottom: 1.25rem;
        }

        .hero h1 .hl {
            color: var(--green);
            position: relative;
        }

        .hero h1 .hl::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--green), var(--green-mid));
            border-radius: 99px;
            opacity: .45;
        }

        .hero-desc {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 2rem;
            max-width: 440px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .btn-big {
            font-size: 15px;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: var(--r-md);
            border: none;
            background: var(--green);
            color: #fff;
            cursor: pointer;
            font-family: var(--font-b);
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-big:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(29, 158, 117, .3);
        }

        .btn-ghost-big {
            font-size: 15px;
            font-weight: 500;
            padding: 13px 24px;
            border-radius: var(--r-md);
            border: 1.5px solid var(--border-md);
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            font-family: var(--font-b);
            transition: all .2s;
        }

        .btn-ghost-big:hover {
            border-color: var(--green);
            color: var(--green);
            background: var(--green-light);
        }

        .hero-trust {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .trust-avatars {
            display: flex;
        }

        .trust-avatars img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #fff;
            margin-left: -8px;
            object-fit: cover;
        }

        .trust-avatars img:first-child {
            margin-left: 0;
        }

        .trust-text {
            font-size: 13px;
            color: var(--text-muted);
        }

        .trust-text strong {
            color: var(--text);
            font-weight: 600;
        }

        /* Hero right image area */
        .hero-right {
            position: relative;
            overflow: hidden;
        }

        .hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            clip-path: polygon(8% 0, 100% 0, 100% 100%, 0 100%);
        }

        .hero-float {
            position: absolute;
            background: #fff;
            border-radius: var(--r-md);
            box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
            padding: 1rem 1.25rem;
        }

        .hero-float1 {
            bottom: 90px;
            left: -10px;
            min-width: 195px;
            animation: float 4s ease-in-out infinite;
        }

        .hero-float2 {
            top: 130px;
            left: 30px;
            min-width: 170px;
            animation: float 4s ease-in-out infinite 2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .hf-label {
            font-size: 11px;
            color: var(--text-hint);
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .hf-val {
            font-family: var(--font-h);
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text);
        }

        .hf-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 99px;
            margin-top: 4px;
        }

        .hf-g {
            background: #e1f5ee;
            color: #0F6E56;
        }

        .hf-b {
            background: #e6f1fb;
            color: #185FA5;
        }

        .hero-stats-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            display: flex;
            border-top: 1px solid var(--border);
        }

        .hs-item {
            flex: 1;
            padding: 1.1rem 1.5rem;
            border-right: 1px solid var(--border);
            text-align: center;
        }

        .hs-item:last-child {
            border-right: none;
        }

        .hs-num {
            font-family: var(--font-h);
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--green-deeper);
        }

        .hs-lbl {
            font-size: 11px;
            color: var(--text-hint);
            margin-top: 1px;
        }

        /* LOGOS BAR */
        .logos-bar {
            background: var(--bg-off);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 2rem;
            text-align: center;
        }

        .logos-label {
            font-size: 11px;
            color: var(--text-hint);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }

        .logos-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            flex-wrap: wrap;
        }

        .lschool {
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: .45;
            filter: grayscale(80%);
            transition: all .2s;
        }

        .lschool:hover {
            opacity: .75;
            filter: grayscale(0);
        }

        .lschool-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            font-family: var(--font-h);
        }

        .lschool-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* SECTIONS */
        .section {
            padding: 6rem 2rem;
        }

        .section-inner {
            max-width: 1160px;
            margin: 0 auto;
        }

        .chip {
            display: inline-block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--green-dark);
            background: var(--green-light);
            padding: 4px 14px;
            border-radius: 99px;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .sec-h {
            font-family: var(--font-h);
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.8px;
            margin-bottom: .75rem;
            line-height: 1.2;
        }

        .sec-p {
            font-size: 1rem;
            color: var(--text-muted);
            max-width: 500px;
            line-height: 1.8;
            margin-bottom: 3rem;
        }

        .c {
            text-align: center;
        }

        .c .sec-p {
            margin: 0 auto 3rem;
        }

        /* Feature split rows */
        .feat-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
            margin-bottom: 6rem;
        }

        .feat-split.rev {
            direction: rtl;
        }

        .feat-split.rev>* {
            direction: ltr;
        }

        .feat-img-wrap {
            border-radius: var(--r-xl);
            overflow: hidden;
            aspect-ratio: 4/3;
            position: relative;
        }

        .feat-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s;
        }

        .feat-img-wrap:hover img {
            transform: scale(1.04);
        }

        .feat-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(8, 80, 65, .5) 0%, transparent 60%);
        }

        .feat-img-badge {
            position: absolute;
            bottom: 1.25rem;
            left: 1.25rem;
            right: 1.25rem;
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(8px);
            border-radius: var(--r-md);
            padding: .85rem 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .fib-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .fi-g {
            background: #e1f5ee;
        }

        .fi-b {
            background: #e6f1fb;
        }

        .fi-a {
            background: #faeeda;
        }

        .fi-p {
            background: #fbeaf0;
        }

        .fib-t1 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .fib-t2 {
            font-size: 11px;
            color: var(--text-hint);
        }

        .feat-content .chip {
            margin-bottom: .75rem;
        }

        .feat-content h3 {
            font-family: var(--font-h);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -.5px;
            margin-bottom: .85rem;
            line-height: 1.25;
        }

        .feat-content>p {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .feat-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 1.75rem;
        }

        .feat-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .feat-list li::before {
            content: '';
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--green-light) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2 6l3 3 5-5' stroke='%230F6E56' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat center;
            display: inline-block;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .feat-link {
            font-size: 14px;
            font-weight: 600;
            color: var(--green);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid var(--green-mid);
            padding-bottom: 1px;
            transition: gap .2s;
        }

        .feat-link:hover {
            gap: 10px;
        }

        /* Cards grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            overflow: hidden;
            transition: transform .2s, box-shadow .2s, border-color .2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 36px rgba(0, 0, 0, .08);
            border-color: var(--green-mid);
        }

        .card-img {
            height: 160px;
            overflow: hidden;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s;
        }

        .card:hover .card-img img {
            transform: scale(1.06);
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: .75rem;
        }

        .card-body h4 {
            font-family: var(--font-h);
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
        }

        .card-body p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* HOW IT WORKS */
        .hiw-bg {
            background: var(--bg-off);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .hiw-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            overflow: hidden;
        }

        .hiw-step {
            padding: 1.75rem;
            border-right: 1px solid var(--border);
        }

        .hiw-step:last-child {
            border-right: none;
        }

        .hiw-img {
            height: 140px;
            border-radius: var(--r-md);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .hiw-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .step-num {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1.5px solid var(--green-mid);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-h);
            font-size: 12px;
            font-weight: 700;
            color: var(--green);
            margin-bottom: .85rem;
        }

        .hiw-step h4 {
            font-family: var(--font-h);
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
        }

        .hiw-step p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* TESTIMONIALS */
        .testi-bg {
            background: var(--bg-dark);
            padding: 6rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .testi-bg::before {
            content: '';
            position: absolute;
            top: -200px;
            right: -200px;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(29, 158, 117, .15) 0%, transparent 70%);
            pointer-events: none;
        }

        .testi-bg .chip {
            background: rgba(29, 158, 117, .15);
            color: var(--green-mid);
        }

        .testi-bg .sec-h {
            color: #fff;
        }

        .testi-bg .sec-p {
            color: rgba(255, 255, 255, .45);
        }

        .testi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .testi-card {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: var(--r-lg);
            padding: 1.6rem;
            transition: background .2s, border-color .2s;
        }

        .testi-card:hover {
            background: rgba(255, 255, 255, .07);
            border-color: rgba(29, 158, 117, .3);
        }

        .testi-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .testi-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid rgba(29, 158, 117, .3);
        }

        .testi-name {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .testi-role {
            font-size: 11px;
            color: rgba(255, 255, 255, .4);
            line-height: 1.5;
        }

        .stars {
            color: var(--amber);
            font-size: 12px;
            letter-spacing: 2px;
            margin-bottom: .85rem;
        }

        .testi-q {
            font-size: 14px;
            color: rgba(255, 255, 255, .6);
            line-height: 1.8;
            font-style: italic;
        }

        /* PRICING */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            max-width: 880px;
            margin: 0 auto;
        }

        .price-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .price-card.featured {
            border: 2px solid var(--green);
            position: relative;
        }

        .price-card.featured::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--green), var(--green-mid));
            border-radius: var(--r-xl) var(--r-xl) 0 0;
        }

        .pop-badge {
            font-size: 11px;
            font-weight: 700;
            color: #0F6E56;
            background: #e1f5ee;
            border-radius: 99px;
            padding: 3px 14px;
            display: inline-block;
            width: fit-content;
            margin-bottom: 1rem;
        }

        .price-name {
            font-family: var(--font-h);
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .price-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 1.25rem;
        }

        .price-amount {
            font-family: var(--font-h);
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .price-amount small {
            font-size: 13px;
            font-weight: 400;
            color: var(--text-hint);
        }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.25rem 0;
        }

        .price-feats {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 9px;
            margin-bottom: 1.75rem;
            flex: 1;
        }

        .price-feats li {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .chk {
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: #e1f5ee url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2 6l3 3 5-5' stroke='%230F6E56' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat center;
            display: inline-block;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .btn-p {
            width: 100%;
            padding: 12px;
            border-radius: var(--r-sm);
            font-size: 14px;
            font-family: var(--font-b);
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
        }

        .btn-solid {
            background: var(--green);
            color: #fff;
            border: none;
        }

        .btn-solid:hover {
            background: var(--green-dark);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border-md);
        }

        .btn-outline:hover {
            border-color: var(--green);
            color: var(--green);
            background: var(--green-light);
        }

        /* CTA BANNER */
        .cta-banner {
            position: relative;
            overflow: hidden;
            background: var(--bg-dark2);
        }

        .cta-img-bg {
            position: absolute;
            inset: 0;
        }

        .cta-img-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .2;
        }

        .cta-img-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(8, 80, 65, .92) 0%, rgba(10, 26, 20, .97) 100%);
        }

        .cta-inner {
            position: relative;
            z-index: 2;
            max-width: 1160px;
            margin: 0 auto;
            padding: 5rem 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
        }

        .cta-left h2 {
            font-family: var(--font-h);
            font-size: clamp(1.9rem, 3vw, 2.6rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .cta-left>p {
            font-size: 1rem;
            color: rgba(255, 255, 255, .55);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .cta-perks {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cta-perks li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, .7);
        }

        .cta-perks li::before {
            content: '';
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(29, 158, 117, .2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: var(--green-mid);
            font-weight: 700;
            flex-shrink: 0;
        }

        .cta-form-box {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: var(--r-xl);
            padding: 2rem;
        }

        .cta-form-box h3 {
            font-family: var(--font-h);
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-bottom: .35rem;
        }

        .cta-form-box>p {
            font-size: 13px;
            color: rgba(255, 255, 255, .4);
            margin-bottom: 1.5rem;
        }

        .cf-field {
            margin-bottom: 12px;
        }

        .cf-label {
            display: block;
            font-size: 12px;
            color: rgba(255, 255, 255, .5);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .cf-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: var(--r-sm);
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .07);
            color: #fff;
            font-size: 14px;
            font-family: var(--font-b);
            outline: none;
            transition: border-color .2s;
        }

        .cf-input::placeholder {
            color: rgba(255, 255, 255, .22);
        }

        .cf-input:focus {
            border-color: var(--green);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            border-radius: var(--r-sm);
            border: none;
            background: var(--green);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: var(--font-b);
            cursor: pointer;
            transition: background .2s, transform .15s;
            margin-top: 4px;
        }

        .btn-submit:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        .cf-note {
            font-size: 11px;
            color: rgba(255, 255, 255, .28);
            text-align: center;
            margin-top: 10px;
        }

        /* FOOTER */
        footer {
            background: var(--bg-dark);
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .footer-top {
            max-width: 1160px;
            margin: 0 auto;
            padding: 4rem 2rem 3rem;
            display: grid;
            grid-template-columns: 2.2fr 1fr 1fr 1.2fr;
            gap: 3rem;
        }

        .footer-logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }

        .footer-logo-img {
            height: 34px;
            width: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .footer-brand-desc {
            font-size: 13.5px;
            color: rgba(255, 255, 255, .38);
            line-height: 1.75;
            max-width: 260px;
            margin-bottom: 1.5rem;
        }

        .footer-social {
            display: flex;
            gap: 9px;
        }

        .social-btn {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .38);
            font-size: 13px;
            font-weight: 700;
            font-family: var(--font-h);
            cursor: pointer;
            transition: all .2s;
        }

        .social-btn:hover {
            border-color: var(--green);
            color: var(--green);
        }

        .footer-col h5 {
            font-family: var(--font-h);
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, .5);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 1.1rem;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-col ul li a {
            font-size: 13.5px;
            color: rgba(255, 255, 255, .38);
            transition: color .2s;
        }

        .footer-col ul li a:hover {
            color: var(--green-mid);
        }

        .footer-contact-items {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .fc-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13px;
            color: rgba(255, 255, 255, .38);
        }

        .fc-icon {
            width: 18px;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .06);
            max-width: 1160px;
            margin: 0 auto;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-copy {
            font-size: 12px;
            color: rgba(255, 255, 255, .22);
        }

        .footer-links-bottom {
            display: flex;
            gap: 1.5rem;
        }

        .footer-links-bottom a {
            font-size: 12px;
            color: rgba(255, 255, 255, .28);
            transition: color .2s;
        }

        .footer-links-bottom a:hover {
            color: rgba(255, 255, 255, .6);
        }

        /* RESPONSIVE */
        @media(max-width:1024px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-right {
                display: none;
            }

            .hero-left {
                padding: 4rem 2rem;
            }

            .feat-split,
            .feat-split.rev {
                grid-template-columns: 1fr;
                direction: ltr;
                gap: 2.5rem;
            }

            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hiw-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hiw-step:nth-child(2) {
                border-right: none;
            }

            .hiw-step:nth-child(1),
            .hiw-step:nth-child(2) {
                border-bottom: 1px solid var(--border);
            }

            .testi-grid {
                grid-template-columns: 1fr 1fr;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .cta-inner {
                grid-template-columns: 1fr;
            }

            .footer-top {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width:768px) {

            .nav-links {
                display: none;
            }

            .nav-right .btn-login {
                display: none;
            }

            .hamburger {
                display: block;
            }
        }

        @media(max-width:640px) {

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .hiw-grid {
                grid-template-columns: 1fr;
            }

            .hiw-step {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .hiw-step:last-child {
                border-bottom: none;
            }

            .testi-grid {
                grid-template-columns: 1fr;
            }

            .footer-top {
                grid-template-columns: 1fr;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header id="site-header">
        <div class="nav-inner">
            <div class="logo">
                <img src="{{ asset('img/logo/uschoollogo.png') }}" alt="uSchool" class="logo-img" />
            </div>
            <nav class="nav-links">
                <a href="#features">Features</a>
                <a href="#how">How it works</a>
                <a href="#pricing">Pricing</a>
                <a href="#testimonials">Reviews</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="nav-right">
                <a href="{{ route('login') }}" class="btn-login">Log in</a>
                <a href="{{ route('register') }}" class="btn-header"><span class="hidden sm:inline">Start Free Trial</span><span class="sm:hidden">Start Free</span> &rarr;</a>
                <button class="hamburger" id="hamburger-btn" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- MOBILE MENU -->
    <div class="mobile-menu" id="mobile-menu">
        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
        <div class="mobile-menu-panel">
            <div class="mobile-menu-header">
                <div class="logo">
                    <img src="{{ asset('img/logo/uschoollogo.png') }}" alt="uSchool" class="logo-img" />
                </div>
                <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Close menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="mobile-menu-links">
                <a href="#features">Features</a>
                <a href="#how">How it works</a>
                <a href="#pricing">Pricing</a>
                <a href="#testimonials">Reviews</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="mobile-menu-actions">
                <a href="{{ route('login') }}" class="btn-login">Log in</a>
                <a href="{{ route('register') }}" class="btn-header">Start Free Trial &rarr;</a>
            </div>
        </div>
    </div>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-left">
            <div class="hero-pill">
                <span class="pill-dot">&#127482;&#127468;</span>
                Built for Uganda &middot; Primary &amp; Secondary Schools
            </div>
            <h1>The <span class="hl">smarter</span> way<br>to run your school</h1>
            <p class="hero-desc">uSchool combines student records, fee management, attendance, grades, and parent communication into one simple platform &mdash; designed specifically for schools from P1 to S6.</p>
            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn-big">Start Free &mdash; 30 Days &rarr;</a>
                <a href="#contact" class="btn-ghost-big">&#9654; Watch Demo</a>
            </div>
            <div class="hero-trust">
                <div class="trust-avatars">
                    <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=64&h=64&fit=crop&crop=face" alt="" />
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=64&h=64&fit=crop&crop=face" alt="" />
                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=64&h=64&fit=crop&crop=face" alt="" />
                </div>
                <div class="trust-text">Trusted by <strong>120+ schools</strong> across Uganda</div>
            </div>
        </div>
        <div class="hero-right">
            <img class="hero-img" src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=900&h=700&fit=crop" alt="Students in a Ugandan classroom" />
            <div class="hero-float hero-float2">
                <div class="hf-label">Today's Attendance</div>
                <div class="hf-val">94.3%</div>
                <span class="hf-badge hf-b">Above average</span>
            </div>
            <div class="hero-float hero-float1">
                <div class="hf-label">Fees Collected</div>
                <div class="hf-val">UGX 42.6M</div>
                <span class="hf-badge hf-g">87% of target</span>
            </div>
            <div class="hero-stats-bar">
                <div class="hs-item">
                    <div class="hs-num">120+</div>
                    <div class="hs-lbl">Schools</div>
                </div>
                <div class="hs-item">
                    <div class="hs-num">48k+</div>
                    <div class="hs-lbl">Students</div>
                </div>
                <div class="hs-item">
                    <div class="hs-num">98%</div>
                    <div class="hs-lbl">Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    {{-- TRUSTED BY — uncomment when real schools are onboarded
    <div class="logos-bar">
        <div class="logos-label">Trusted by schools across Uganda</div>
        <div class="logos-row">
            <div class="lschool">
                <div class="lschool-icon" style="background:#e1f5ee;color:#085041;">SJ</div><span class="lschool-name">St. Jude's Primary</span>
            </div>
            <div class="lschool">
                <div class="lschool-icon" style="background:#e6f1fb;color:#0C447C;">HS</div><span class="lschool-name">Hope Secondary</span>
            </div>
            <div class="lschool">
                <div class="lschool-icon" style="background:#faeeda;color:#633806;">KJ</div><span class="lschool-name">Kampala Junior Academy</span>
            </div>
            <div class="lschool">
                <div class="lschool-icon" style="background:#fbeaf0;color:#4B1528;">MH</div><span class="lschool-name">Makerere Hill School</span>
            </div>
            <div class="lschool">
                <div class="lschool-icon" style="background:#e1f5ee;color:#085041;">NP</div><span class="lschool-name">Nakivubo Primary</span>
            </div>
            <div class="lschool">
                <div class="lschool-icon" style="background:#e6f1fb;color:#0C447C;">EG</div><span class="lschool-name">Entebbe Grammar</span>
            </div>
        </div>
    </div>
    --}}

    <!-- FEATURES -->
    <section class="section" id="features">
        <div class="section-inner">
            <div class="c" style="margin-bottom:4rem;">
                <div class="chip">Features</div>
                <div class="sec-h">Everything your school needs</div>
                <p class="sec-p">From the first student enrolled to the last report card printed &mdash; uSchool handles it all in one place.</p>
            </div>

            <div class="feat-split">
                <div class="feat-img-wrap">
                    <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=600&fit=crop" alt="Students learning" />
                    <div class="feat-overlay"></div>
                    <div class="feat-img-badge">
                        <div class="fib-icon fi-g">&#128100;</div>
                        <div>
                            <div class="fib-t1">1,248 students enrolled</div>
                            <div class="fib-t2">Updated this term</div>
                        </div>
                    </div>
                </div>
                <div class="feat-content">
                    <div class="chip">Student Management</div>
                    <h3>Every student's story, in one place</h3>
                    <p>Maintain complete profiles for every student &mdash; from P1 to S6. Track academic history, manage class placement, and access records instantly without digging through filing cabinets.</p>
                    <ul class="feat-list">
                        <li>Digital student profiles with photos and family contacts</li>
                        <li>Class placement and promotion history across all terms</li>
                        <li>Academic records, disciplinary notes, and health info</li>
                        <li>Instant search and filter by name, class, or ID number</li>
                    </ul>
                    <a class="feat-link" href="#contact">Explore student management &rarr;</a>
                </div>
            </div>

            <div class="feat-split rev">
                <div class="feat-img-wrap">
                    <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&h=600&fit=crop" alt="Fee management finances" />
                    <div class="feat-overlay"></div>
                    <div class="feat-img-badge">
                        <div class="fib-icon fi-a">&#128179;</div>
                        <div>
                            <div class="fib-t1">UGX 42.6M collected</div>
                            <div class="fib-t2">87% of term target</div>
                        </div>
                    </div>
                </div>
                <div class="feat-content">
                    <div class="chip">Fee Management</div>
                    <h3>Collect fees faster, chase less</h3>
                    <p>Set up fee structures once and let the system handle the rest. Record payments, track balances, and send automatic reminders to parents &mdash; no more manual spreadsheets or endless phone calls.</p>
                    <ul class="feat-list">
                        <li>Customizable fee structures per class and per term</li>
                        <li>Mobile money, bank transfer and cash payment recording</li>
                        <li>Automatic SMS reminders for outstanding balances</li>
                        <li>One-click fee statements and receipts for parents</li>
                    </ul>
                    <a class="feat-link" href="#contact">Explore fee management &rarr;</a>
                </div>
            </div>

            <div class="feat-split">
                <div class="feat-img-wrap">
                    <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800&h=600&fit=crop" alt="Teacher and students communication" />
                    <div class="feat-overlay"></div>
                    <div class="feat-img-badge">
                        <div class="fib-icon fi-b">&#128241;</div>
                        <div>
                            <div class="fib-t1">23 parent alerts sent today</div>
                            <div class="fib-t2">Attendance &amp; fees</div>
                        </div>
                    </div>
                </div>
                <div class="feat-content">
                    <div class="chip">Attendance &amp; Communication</div>
                    <h3>Keep parents informed, automatically</h3>
                    <p>Mark attendance in seconds and let the system instantly notify parents when their child is absent. Send fee alerts, school announcements, and report card notifications directly to phones.</p>
                    <ul class="feat-list">
                        <li>Per-class daily attendance with one-tap marking</li>
                        <li>Instant SMS alerts to parents on absences</li>
                        <li>Broadcast school announcements to all parents</li>
                        <li>Parent portal to view attendance and fee balance</li>
                    </ul>
                    <a class="feat-link" href="#contact">Explore communication tools &rarr;</a>
                </div>
            </div>

            <div class="cards-grid">
                <div class="card">
                    <div class="card-img"><img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&h=320&fit=crop" alt="Gradebook marks" /></div>
                    <div class="card-body">
                        <div class="card-icon" style="background:#e1f5ee;">&#128202;</div>
                        <h4>Gradebook &amp; Report Cards</h4>
                        <p>Enter marks once and auto-generate print-ready report cards for every student, formatted for Uganda's curriculum.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-img"><img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&h=320&fit=crop" alt="Analytics dashboard" /></div>
                    <div class="card-body">
                        <div class="card-icon" style="background:#e6f1fb;">&#128200;</div>
                        <h4>Admin Dashboard</h4>
                        <p>A live overview of everything &mdash; fee collection rates, attendance trends, and key performance metrics at a glance.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-img"><img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?w=600&h=320&fit=crop" alt="Teachers in class" /></div>
                    <div class="card-body">
                        <div class="card-icon" style="background:#fbeaf0;">&#128105;&#8205;&#127979;</div>
                        <h4>Staff Management</h4>
                        <p>Manage teacher profiles, subject assignments, and class timetables &mdash; all inside the same platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="section hiw-bg" id="how">
        <div class="section-inner">
            <div class="c" style="margin-bottom:3rem;">
                <div class="chip">How it works</div>
                <div class="sec-h">Up and running in 4 simple steps</div>
                <p class="sec-p">No IT team, no complicated setup. Just open a browser and start managing your school today.</p>
            </div>
            <div class="hiw-grid">
                <div class="hiw-step">
                    <div class="hiw-img"><img src="https://images.unsplash.com/photo-1497032628192-86f99bcd76bc?w=400&h=280&fit=crop" alt="Register online" /></div>
                    <div class="step-num">1</div>
                    <h4>Register your school</h4>
                    <p>Sign up in minutes. Add your school name, term dates, and class structure once &mdash; you're ready to go.</p>
                </div>
                <div class="hiw-step">
                    <div class="hiw-img"><img src="https://images.unsplash.com/photo-1512314889357-e157c22f938d?w=400&h=280&fit=crop" alt="Import students data" /></div>
                    <div class="step-num">2</div>
                    <h4>Import your students</h4>
                    <p>Upload an Excel sheet or add students manually. The system organizes them into classes automatically.</p>
                </div>
                <div class="hiw-step">
                    <div class="hiw-img"><img src="https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=400&h=280&fit=crop" alt="Manage school daily" /></div>
                    <div class="step-num">3</div>
                    <h4>Start managing</h4>
                    <p>Record attendance, collect fees, enter marks, and message parents &mdash; all from one clean dashboard.</p>
                </div>
                <div class="hiw-step">
                    <div class="hiw-img"><img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400&h=280&fit=crop" alt="Review school reports" /></div>
                    <div class="step-num">4</div>
                    <h4>Track &amp; improve</h4>
                    <p>Use live reports to spot trends, follow up on outstanding fees, and keep leadership updated with real data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="testi-bg" id="testimonials">
        <div class="section-inner">
            <div class="c" style="margin-bottom:3rem;">
                <div class="chip">Testimonials</div>
                <div class="sec-h">What school leaders are saying</div>
                <p class="sec-p">Used by headmasters, bursars, and directors across Uganda every single school day.</p>
            </div>
            <div class="testi-grid">
                <div class="testi-card">
                    <div class="testi-top">
                        <img class="testi-avatar" src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=92&h=92&fit=crop&crop=face" alt="Margaret K" />
                        <div>
                            <div class="testi-name">Margaret Kyomugisha</div>
                            <div class="testi-role">Headmistress<br>St. Jude's Primary, Mbarara</div>
                        </div>
                    </div>
                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p class="testi-q">"Fee collection used to take our bursar three full days every term. With uSchool it's updated in real time and parents can pay via mobile money. It has completely transformed how we operate."</p>
                </div>
                <div class="testi-card">
                    <div class="testi-top">
                        <img class="testi-avatar" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=92&h=92&fit=crop&crop=face" alt="James Okello" />
                        <div>
                            <div class="testi-name">James Okello</div>
                            <div class="testi-role">Director<br>Hope Secondary School, Gulu</div>
                        </div>
                    </div>
                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p class="testi-q">"The attendance SMS feature has dramatically improved punctuality in our school. Parents respond within minutes when they get a message that their child is absent. Every school should have this."</p>
                </div>
                <div class="testi-card">
                    <div class="testi-top">
                        <img class="testi-avatar" src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=92&h=92&fit=crop&crop=face" alt="Patricia Namukasa" />
                        <div>
                            <div class="testi-name">Patricia Namukasa</div>
                            <div class="testi-role">Director of Studies<br>Kampala Junior Academy</div>
                        </div>
                    </div>
                    <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p class="testi-q">"Generating end-of-term report cards used to take us two weeks. Now I do the entire school in one afternoon. Even teachers who aren't tech-savvy can navigate the gradebook without help."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING -->
    <section class="section" id="pricing">
        <div class="section-inner">
            <div class="c" style="margin-bottom:3rem;">
                <div class="chip">Pricing</div>
                <div class="sec-h">Simple, school-friendly plans</div>
                <p class="sec-p">Priced in UGX. Cancel any time. No setup fees. No hidden charges. Full support on every plan.</p>
            </div>
            <div class="pricing-grid">
                <div class="price-card">
                    <div class="price-name">Starter</div>
                    <div class="price-desc">For small schools up to 200 students</div>
                    <div class="price-amount">UGX 750,000 <small>/ year</small></div>
                    <hr class="divider" />
                    <ul class="price-feats">
                        <li><span class="chk"></span>Up to 200 students</li>
                        <li><span class="chk"></span>Student &amp; fee management</li>
                        <li><span class="chk"></span>Attendance tracking</li>
                        <li><span class="chk"></span>Basic report cards</li>
                        <li><span class="chk"></span>50 SMS alerts / month</li>
                        <li><span class="chk"></span>Email support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn-p btn-outline">Get started free</a>
                </div>
                <div class="price-card featured">
                    <div class="pop-badge">Most popular</div>
                    <div class="price-name">Growth</div>
                    <div class="price-desc">For growing schools up to 800 students</div>
                    <div class="price-amount">UGX 1,300,000 <small>/ year</small></div>
                    <hr class="divider" />
                    <ul class="price-feats">
                        <li><span class="chk"></span>Up to 800 students</li>
                        <li><span class="chk"></span>All Starter features</li>
                        <li><span class="chk"></span>Parent communication portal</li>
                        <li><span class="chk"></span>Advanced gradebook</li>
                        <li><span class="chk"></span>300 SMS alerts / month</li>
                        <li><span class="chk"></span>WhatsApp &amp; phone support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn-p btn-solid">Get started free</a>
                </div>
                <div class="price-card">
                    <div class="price-name">School Group</div>
                    <div class="price-desc">Multiple campuses or 800+ students</div>
                    <div class="price-amount">Custom</div>
                    <hr class="divider" />
                    <ul class="price-feats">
                        <li><span class="chk"></span>Unlimited students</li>
                        <li><span class="chk"></span>Multi-campus dashboard</li>
                        <li><span class="chk"></span>Custom integrations</li>
                        <li><span class="chk"></span>Unlimited SMS</li>
                        <li><span class="chk"></span>Dedicated account manager</li>
                        <li><span class="chk"></span>On-site training session</li>
                    </ul>
                    <a href="#contact" class="btn-p btn-outline">Talk to our team</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <div class="cta-banner" id="contact">
        <div class="cta-img-bg">
            <img src="https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?w=1400&h=700&fit=crop" alt="School background" />
        </div>
        <div class="cta-inner">
            <div class="cta-left">
                <h2>Start managing your school the smart way</h2>
                <p>Join over 120 schools already saving time, reducing paperwork, and keeping parents informed &mdash; all with uSchool.</p>
                <ul class="cta-perks">
                    <li>Free 30-day trial &mdash; no credit card required</li>
                    <li>Setup assistance from our Kampala team</li>
                    <li>Your data stays in Uganda &mdash; secure and private</li>
                    <li>Works on any phone, tablet, or computer</li>
                    <li>Training provided for your entire staff</li>
                </ul>
            </div>
            <div class="cta-form-box">
                <h3>Request a free demo</h3>
                <p>We'll reach out within one working day.</p>
                @if(session('success'))
                <div style="background:var(--green-light);color:var(--green-dark);padding:12px 16px;border-radius:var(--r-sm);font-size:14px;font-weight:500;margin-bottom:1rem;">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                <div style="background:#fbeaf0;color:#9b1c31;padding:12px 16px;border-radius:var(--r-sm);font-size:13px;margin-bottom:1rem;">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
                @endif
                <form method="POST" action="{{ route('demo.request') }}">
                    @csrf
                    <div class="cf-field">
                        <label class="cf-label">School name</label>
                        <input class="cf-input" type="text" name="school_name" placeholder="e.g. St. Mary's College Kisubi" value="{{ old('school_name') }}" required />
                    </div>
                    <div class="cf-field">
                        <label class="cf-label">Your name &amp; role</label>
                        <input class="cf-input" type="text" name="contact_name" placeholder="e.g. John Ssali — Headmaster" value="{{ old('contact_name') }}" required />
                    </div>
                    <div class="cf-field">
                        <label class="cf-label">WhatsApp number</label>
                        <input class="cf-input" type="tel" name="whatsapp" placeholder="+256 776 121 422" value="{{ old('whatsapp') }}" required />
                    </div>
                    <button type="submit" class="btn-submit">Request Free Demo &rarr;</button>
                </form>
                <div class="cf-note">No spam. No commitment. Just a friendly call from our team.</div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-top">
            <div class="footer-brand">
                <div class="footer-logo-row">
                    <img src="{{ asset('img/logo/uschoollogo.png') }}" alt="uSchool" class="footer-logo-img" />
                </div>
                <p class="footer-brand-desc">The all-in-one school management platform built specifically for Ugandan Primary and Secondary schools. Simple, affordable, and backed by a local team in Kampala.</p>
                <div class="footer-social">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-btn">f</a>
                    <a href="https://x.com" target="_blank" rel="noopener noreferrer" class="social-btn">&#120143;</a>
                    <a href="https://wa.me/256776121422" target="_blank" rel="noopener noreferrer" class="social-btn">W</a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="social-btn">in</a>
                </div>
            </div>
            <div class="footer-col">
                <h5>Product</h5>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="{{ route('pages.privacy') }}">Privacy &amp; Security</a></li>
                    <li><a href="{{ route('pages.terms') }}">Terms of Service</a></li>
                    <li><a href="{{ route('pages.cookies') }}">Cookie Policy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Resources</h5>
                <ul>
                    <li><a href="#contact">Help Center</a></li>
                    <li><a href="#how">Getting Started</a></li>
                    <li><a href="#features">Feature Guide</a></li>
                    <li><a href="#testimonials">Customer Stories</a></li>
                    <li><a href="#contact">Contact Support</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Contact Us</h5>
                <div class="footer-contact-items">
                    <div class="fc-item"><span class="fc-icon">&#128205;</span><span>Kampala, Uganda</span></div>
                    <div class="fc-item"><span class="fc-icon">&#128222;</span><span>+256 776 121 422</span></div>
                    <div class="fc-item"><span class="fc-icon">&#9993;&#65039;</span><span>uschool@techmarketug.com</span></div>
                    <div class="fc-item"><span class="fc-icon">&#128172;</span><span>WhatsApp Support<br>Mon&ndash;Fri, 8am&ndash;6pm</span></div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-copy">&copy; {{ date('Y') }} uSchool &middot; All rights reserved &middot; Made with &#10084;&#65039; in Uganda &#127482;&#127468;</div>
            <div class="footer-links-bottom">
                <a href="{{ route('pages.privacy') }}">Privacy Policy</a>
                <a href="{{ route('pages.terms') }}">Terms of Service</a>
                <a href="{{ route('pages.cookies') }}">Cookie Policy</a>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        const hdr = document.getElementById('site-header');
        window.addEventListener('scroll', () => {
            hdr.classList.toggle('scrolled', window.scrollY > 30);
        }, {
            passive: true
        });

        // Mobile menu
        const mobileMenu = document.getElementById('mobile-menu');
        const openBtn = document.getElementById('hamburger-btn');
        const closeBtn = document.getElementById('mobile-menu-close');
        const overlay = document.getElementById('mobile-menu-overlay');

        function openMobileMenu() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        }

        openBtn.addEventListener('click', openMobileMenu);
        closeBtn.addEventListener('click', closeMobileMenu);
        overlay.addEventListener('click', closeMobileMenu);

        mobileMenu.querySelectorAll('.mobile-menu-links a').forEach(function(link) {
            link.addEventListener('click', closeMobileMenu);
        });
    </script>
</body>

</html>