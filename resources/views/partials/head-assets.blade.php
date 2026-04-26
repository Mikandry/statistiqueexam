@if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
<style>
    :root {
        --app-font-sans: "Avenir Next", "Segoe UI", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
        --app-font-display: "Avenir Next", "Arial Narrow", "Segoe UI", Inter, ui-sans-serif, system-ui, sans-serif;
    }

    body {
        font-family: var(--app-font-sans);
    }
</style>
