<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Service de l. organisation des examens') }} · @yield('title', 'Service de lorganisation des Examens')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.head-assets')

    <style>
        :root {
            --app-font-sans: "Figtree", "Avenir Next", "Segoe UI", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            --app-font-display: "Figtree", "Avenir Next", "Arial Narrow", "Segoe UI", Inter, ui-sans-serif, system-ui, sans-serif;
        }

        body {
            font-family: var(--app-font-sans);
        }

        .app-sidebar-shell {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 50;
            height: 100vh;
            width: 280px;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .app-sidebar-shell[data-sidebar-state="expanded"] {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            .app-sidebar-shell {
                position: static;
                transform: translateX(0);
            }
        }

        .app-sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 40;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .app-sidebar-overlay[data-sidebar-state="expanded"] {
            opacity: 1;
            visibility: visible;
        }

        @media (min-width: 1024px) {
            .app-sidebar-overlay {
                display: none;
            }
        }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-900">
    <!-- Sidebar Overlay -->
    <div class="app-sidebar-overlay lg:hidden" id="appSidebarOverlay" data-sidebar-state="collapsed"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-h-screen lg:ml-0">
            <!-- Header -->
            <header class="bg-white border-b border-slate-200 px-6 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button type="button" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition-colors" id="appSidebarToggleMobile" aria-label="Afficher le menu latéral">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900">@yield('title', 'Service des Examens')</h1>
                            @hasSection('subtitle')
                                <p class="text-sm text-slate-500 mt-1">@yield('subtitle')</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        @if(auth()->check())
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-slate-600 font-medium">
                                    {{ auth()->user()->name }}
                                </span>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-slate-600 hover:text-slate-900 transition-colors font-medium">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-auto">
                <div class="p-6 lg:p-8">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    @stack('scripts')

    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('appSidebarShell');
            const overlay = document.getElementById('appSidebarOverlay');
            const toggleMobile = document.getElementById('appSidebarToggleMobile');

            function toggleSidebar() {
                const isExpanded = sidebar.dataset.sidebarState === 'expanded';
                sidebar.dataset.sidebarState = isExpanded ? 'collapsed' : 'expanded';
                overlay.dataset.sidebarState = isExpanded ? 'collapsed' : 'expanded';
            }

            toggleMobile?.addEventListener('click', toggleSidebar);
            overlay?.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>