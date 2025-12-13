@props(['title' => null])

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{$title}}</title>

    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-neutral-950 antialiased">
{{-- Reusable Header Component --}}
<x-layouts.auth.header />

<main class="pt-16">
    {{ $slot }}
</main>
<x-personal.footer />
</body>
</html>
