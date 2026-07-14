<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.head-assets')
</head>
<body class="antialiased bg-slate-50">
    <div class="app-shell min-h-screen flex">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Main Content -->
        <main class="app-main flex-1 flex flex-col min-h-screen">
            <!-- Header -->
            <header class="app-header bg-white border-b border-slate-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button type="button" class="app-sidebar-toggle-mobile lg:hidden p-2 rounded-lg hover:bg-slate-100" id="appSidebarToggleMobile">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-semibold text-slate-900">
                            @yield('title', 'Service des Examens')
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">
                        @if(auth()->check())
                            <span class="text-sm text-slate-600">
                                {{ auth()->user()->name }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-slate-600 hover:text-slate-900">
                                    Déconnexion
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="app-content flex-1 p-6">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>