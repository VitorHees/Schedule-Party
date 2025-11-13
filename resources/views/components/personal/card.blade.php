@props([
    'title' => '',
    'price' => null, // null => "Custom"
    'suffix' => '/month',
    'featured' => false,
    'ctaLabel' => 'Choose Plan',
    'ctaHref' => '#',
    'perks' => [], // array of strings
])

@php
    $wrap = $featured
        ? 'p-8 rounded-2xl bg-gradient-to-br from-purple-600 to-blue-600 text-white relative transform md:scale-105 shadow-2xl'
        : 'p-8 rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-600 dark:hover:border-purple-400 transition';

    $buttonClasses = $featured
        ? 'block w-full py-3 text-center font-semibold text-purple-600 bg-white hover:bg-gray-100 rounded-lg transition'
        : 'block w-full py-3 text-center font-semibold text-purple-600 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 rounded-lg transition';
@endphp

<div class="{{ $wrap }}">
    @if($featured)
        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-purple-900 px-4 py-1 rounded-full text-sm font-bold">
            POPULAR
        </div>
    @endif

    <h3 class="text-2xl font-bold {{ $featured ? '' : 'text-gray-900 dark:text-white' }} mb-2">{{ $title }}</h3>

    <div class="text-4xl font-bold mb-4">
        @if(!is_null($price))
            ${{ $price }}<span class="text-lg {{ $featured ? 'opacity-80' : 'text-gray-600 dark:text-gray-400' }}">{{ $suffix }}</span>
        @else
            <span class="{{ $featured ? '' : 'text-gray-900 dark:text-white' }}">Custom</span>
        @endif
    </div>

    <p class="{{ $featured ? 'opacity-90' : 'text-gray-600 dark:text-gray-400' }} mb-6">
        {{ $featured ? 'For power users and teams' : 'Perfect for personal use' }}
    </p>

    <ul class="space-y-3 mb-8">
        @foreach($perks as $perk)
            <li class="flex items-center gap-2">
                <svg class="w-5 h-5 {{ $featured ? '' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="{{ $featured ? '' : 'text-gray-700 dark:text-gray-300' }}">{{ $perk }}</span>
            </li>
        @endforeach
    </ul>

    <a href="{{ $ctaHref }}" class="{{ $buttonClasses }}">{{ $ctaLabel }}</a>
</div>
