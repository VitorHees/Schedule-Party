@props([
    'variant' => 'primary', // primary | secondary | ghost
    'href' => null,
    'type' => 'button',
])

@php
    // Base layout + focus styles
    $base = 'inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    // Default color sets per variant
    $variants = [
        'primary'   => 'text-white bg-purple-600 hover:bg-purple-700 focus:ring-purple-600',
        'secondary' => 'text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800 border-2 border-purple-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-purple-600',
        'ghost'     => 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:ring-gray-500',
    ];

    $userClasses = (string) $attributes->get('class', '');

    // Match ONLY color utilities, not sizes like text-lg
    $textColorRegex = '/(^|\\s)(?:dark:)?text-(?:inherit|current|white|black|slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)(?:-\\d{2,3})?(?=\\s|$)/';
    $bgColorRegex   = '/(^|\\s)(?:dark:)?bg-(?:transparent|inherit|current|white|black|slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)(?:-\\d{2,3})?(?=\\s|$)/';

    $hasUserTextColor = preg_match($textColorRegex, $userClasses) === 1;
    $hasUserBgColor   = preg_match($bgColorRegex, $userClasses) === 1;

    // Only omit default colors if caller provided explicit color utilities
    $colorClasses = ($hasUserTextColor || $hasUserBgColor) ? '' : ($variants[$variant] ?? $variants['primary']);

    $classes = trim("$base $colorClasses");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
