@props([
    'title' => '',
    'preset' => 'purple', // purple | blue | green | yellow | pink | indigo
])

@php
    $map = [
        'purple' => ['wrap' => 'from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20', 'icon' => 'bg-purple-600'],
        'blue'   => ['wrap' => 'from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20',       'icon' => 'bg-blue-600'],
        'green'  => ['wrap' => 'from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20',    'icon' => 'bg-green-600'],
        'yellow' => ['wrap' => 'from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20','icon' => 'bg-yellow-600'],
        'pink'   => ['wrap' => 'from-pink-50 to-pink-100 dark:from-pink-900/20 dark:to-pink-800/20',        'icon' => 'bg-pink-600'],
        'indigo' => ['wrap' => 'from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20','icon' => 'bg-indigo-600'],
    ];
    $c = $map[$preset] ?? $map['purple'];
@endphp

<div class="p-6 rounded-xl bg-gradient-to-br {{ $c['wrap'] }} hover:shadow-lg transition">
    <div class="w-12 h-12 rounded-lg {{ $c['icon'] }} flex items-center justify-center mb-4">
        {{ $icon ?? '' }}
    </div>
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $title }}</h3>
    <p class="text-gray-600 dark:text-gray-400">{{ $slot }}</p>
</div>
