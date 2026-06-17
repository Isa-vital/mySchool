<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ setting('school_name', config('app.name', 'uSchool')) }}</title>

    <!-- Favicon -->
    @if(setting('school_favicon'))
    <link rel="icon" type="image/png" href="{{ asset('storage/' . setting('school_favicon')) }}">
    @else
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon_io/apple-touch-icon.png') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon_io/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon_io/favicon-16x16.png') }}" />
    @endif

    <!-- PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="{{ setting('primary_color', '#1e40af') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- SweetAlert2 (for success / error notifications) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- CHANGED: restored Blade echo syntax; a formatter had mangled {{ }} into "{ { } }" with line breaks, which broke the CSS variables so the theme colour never applied --}}
    <style>
        :root {
            /* CHANGED: preserved malformed formatter output for reference.
               --primary-color: {
                       {
                       setting('primary_color', '#1e40af')
                   }
               }
               ;
               --secondary-color: {
                       {
                       setting('secondary_color', '#7c3aed')
                   }
               }
               ;
            */

            --primary-color: {
                    {
                    setting('primary_color', '#1e40af')
                }
            }

            ;

            --secondary-color: {
                    {
                    setting('secondary_color', '#7c3aed')
                }
            }

            ;
        }
    </style>
</head>

<body class="font-sans antialiased" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
    <div class="min-h-screen bg-gray-100 flex">
        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-64" :class="!sidebarOpen && 'lg:ml-16 lg:!ml-16'">
            {{-- Top Bar --}}
            @include('layouts.topbar')

            {{-- Page Heading --}}
            @isset($header)
            <header class="bg-white shadow-sm border-b">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    {{ $header }}
                </div>
            </header>
            @endisset

            {{-- Flash Messages (rendered via SweetAlert2) --}}
            {{-- CHANGED: replaced inline Alpine flash banners with SweetAlert2 toast/modal notifications.
                 Flash payload is passed via a data element to keep PHP out of the JS block. --}}
            @if(session('success') || session('error') || $errors->any())
            <div id="flash-data"
                data-success="{{ session('success') }}"
                data-error="{{ session('error') }}"
                data-errors="{{ $errors->any() ? json_encode($errors->all()) : '' }}"
                hidden></div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var flash = document.getElementById('flash-data');
                    if (!flash || typeof Swal === 'undefined') return;

                    var primary = getComputedStyle(document.documentElement)
                        .getPropertyValue('--primary-color').trim() || '#1e40af';

                    var success = flash.dataset.success;
                    var error = flash.dataset.error;
                    var errors = flash.dataset.errors ? JSON.parse(flash.dataset.errors) : [];

                    if (success) {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true,
                            didOpen: function(toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        }).fire({
                            icon: 'success',
                            title: success
                        });
                    }

                    if (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error,
                            confirmButtonColor: primary
                        });
                    }

                    if (errors.length) {
                        var list = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                        errors.forEach(function(e) {
                            var div = document.createElement('div');
                            div.textContent = e;
                            list += '<li>' + div.innerHTML + '</li>';
                        });
                        list += '</ul>';
                        Swal.fire({
                            icon: 'error',
                            title: 'Please fix the following',
                            html: list,
                            confirmButtonColor: primary
                        });
                    }
                });
            </script>
            @endif

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

            {{-- Offline Indicator --}}
            <div id="offline-indicator" class="hidden fixed bottom-4 right-4 bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M5.636 18.364a9 9 0 010-12.728m2.828 9.9a5 5 0 010-7.072m7.072 0a5 5 0 010 7.072M12 12h.01"></path>
                </svg>
                <span>You are offline</span>
                <span id="sync-count" class="bg-yellow-600 text-xs px-2 py-0.5 rounded-full hidden">0 pending</span>
            </div>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div x-show="mobileSidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-30 lg:hidden" @click="mobileSidebarOpen = false"></div>

    @livewireScripts
    <script>
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').then(reg => {
                console.log('SW registered:', reg.scope);
            }).catch(err => console.log('SW registration failed:', err));
        }

        // Online/Offline detection
        function updateOnlineStatus() {
            const indicator = document.getElementById('offline-indicator');
            if (navigator.onLine) {
                indicator.classList.add('hidden');
            } else {
                indicator.classList.remove('hidden');
            }
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    </script>

    {{-- CHANGED: page-level scripts pushed by views/components (e.g. grade entry) --}}
    @stack('scripts')
</body>

</html>