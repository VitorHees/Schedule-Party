<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Schedule Party') }}</title>

    {{-- CRITICAL: Dark mode script MUST run before any rendering --}}
    <script>
        // Immediately check localStorage and apply dark class
        const darkMode = localStorage.getItem('darkMode');

        // Default to dark if no preference set, otherwise respect saved preference
        if (darkMode === 'true' || darkMode === null) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-neutral-950 antialiased">
{{-- Reusable Header Component --}}
<x-layouts.auth.header />

{{-- Main Content with top padding to account for fixed header --}}
<main class="pt-16">
    {{ $slot }}
</main>

{{-- Reusable Footer Component --}}
<x-personal.footer />

@fluxScripts
</body>
</html>
