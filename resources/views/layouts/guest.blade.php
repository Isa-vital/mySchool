<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'uSchool') }} – Login</title>

    {{-- SEO --}}
    <meta name="description" content="Log in to uSchool — the smarter way to manage your school. Access student records, fees, attendance, and grades from one dashboard." />
    <meta name="robots" content="noindex, nofollow" />

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon_io/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon_io/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon_io/favicon-16x16.png') }}" />
    <link rel="manifest" href="{{ asset('img/favicon_io/site.webmanifest') }}" />
    <meta name="theme-color" content="#1D9E75" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex">
        {{-- Left Panel — Branding (desktop only) --}}
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-900 relative overflow-hidden flex-col justify-between p-12">
            {{-- Decorative circles --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-3xl"></div>

            {{-- Logo --}}
            <div class="relative z-10">
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('img/logo/uschoollogo.png') }}" alt="uSchool" class="h-12 object-contain" />
                    <span class="text-white font-semibold text-lg">uSchool</span>
                </a>
            </div>

            {{-- Features --}}
            <div class="relative z-10 space-y-8">
                <h2 class="text-3xl xl:text-4xl font-bold text-white leading-tight">The smarter way<br>to run your school</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-emerald-400/30 flex items-center justify-center mt-0.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="text-emerald-100 text-sm">Student records, fees, grades and attendance — all in one place</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-emerald-400/30 flex items-center justify-center mt-0.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="text-emerald-100 text-sm">Parent &amp; teacher portals for seamless communication</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-emerald-400/30 flex items-center justify-center mt-0.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="text-emerald-100 text-sm">Designed specifically for Ugandan primary &amp; secondary schools</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="relative z-10">
                <p class="text-emerald-200/60 text-xs">&copy; {{ date('Y') }} uSchool. All rights reserved.</p>
            </div>
        </div>

        {{-- Right Panel — Form --}}
        <div class="w-full lg:w-1/2 flex flex-col bg-white">
            {{-- Mobile header --}}
            <div class="lg:hidden flex items-center justify-between p-4 border-b border-gray-100">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('img/logo/uschoollogo.png') }}" alt="uSchool" class="h-8 object-contain" />
                    <span class="font-semibold text-gray-800">uSchool</span>
                </a>
                <a href="/" class="text-xs text-gray-400 hover:text-emerald-600 transition-colors">&larr; Back to home</a>
            </div>

            <div class="flex-1 flex items-center justify-center px-6 py-12 sm:px-12">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>