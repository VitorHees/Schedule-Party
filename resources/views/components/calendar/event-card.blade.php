@props(['event'])

@php
    $groupColor = $event->mixed_color ?? $event->groups->first()->color ?? '#A855F7';
    $isRepeating = $event->repeat_frequency !== 'none';
    $images = $event->images['urls'] ?? [];

    // Construct Badges
    $badges = collect();
    foreach($event->groups as $group) {
        $badges->push([
            'text' => $group->name,
            'style' => "background-color: {$group->color}10; color: {$group->color}; ring-color: {$group->color}20;",
            'classes' => 'ring-1 ring-inset',
        ]);
    }
    if($event->is_nsfw) {
        $badges->push([
            'text' => 'NSFW',
            'classes' => 'border border-red-200 bg-red-50 text-red-600 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400',
            'icon' => 'heroicon-s-exclamation-triangle'
        ]);
    }
    foreach($event->genders ?? [] as $gender) {
        $badges->push([
            'text' => $gender->name,
            'classes' => 'border border-teal-200 bg-teal-50 text-teal-600 dark:border-teal-800 dark:bg-teal-900/20 dark:text-teal-400',
            'icon' => 'heroicon-s-user'
        ]);
    }
    if($event->min_age) {
        $badges->push([
            'text' => $event->min_age . '+',
            'classes' => 'border border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400',
            'icon' => 'heroicon-s-cake'
        ]);
    }
    if($event->max_distance_km) {
        $badges->push([
            'text' => $event->max_distance_km . 'KM',
            'classes' => 'border border-indigo-200 bg-indigo-50 text-indigo-600 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400',
            'icon' => 'heroicon-s-map'
        ]);
    }
@endphp

<div class="group relative flex flex-col md:flex-row items-stretch overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:-translate-y-0.5 hover:border-purple-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
    <div class="absolute left-0 top-0 bottom-0 w-1.5 md:static md:w-1.5 shrink-0" style="background: {{ $groupColor }}"></div>

    <div class="flex-1 flex flex-col md:flex-row p-6 gap-6">
        {{-- Time --}}
        <div class="flex flex-col items-start min-w-[80px]">
            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }}</span>
            @if(!$event->is_all_day)
                <span class="text-xs font-medium text-gray-400">{{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}</span>
            @endif
            @if($isRepeating)
                <x-heroicon-s-arrow-path class="w-3 h-3 text-gray-400 mt-1" title="Repeating" />
            @endif
        </div>

        {{-- Details --}}
        <div class="flex-1 space-y-2">
            <div x-data="{ expanded: false }" class="flex flex-wrap items-center gap-2 mb-1">
                @foreach($badges as $index => $badge)
                    <span
                        x-show="expanded || {{ $index }} < 3"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badge['classes'] }}"
                        style="{{ $badge['style'] ?? '' }}"
                    >
                        @if(isset($badge['icon'])) <x-dynamic-component :component="$badge['icon']" class="h-3 w-3" /> @endif
                        {{ $badge['text'] }}
                    </span>
                @endforeach
                @if($badges->count() > 3)
                    <button @click.prevent.stop="expanded = !expanded" class="text-[10px] font-bold text-gray-400 hover:text-purple-600 transition-colors">
                        <span x-show="!expanded">+{{ $badges->count() - 3 }}</span>
                        <span x-show="expanded">Less</span>
                    </button>
                @endif
            </div>

            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $event->name }}</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $event->description }}</p>

            <div class="pt-2 flex flex-wrap gap-4">
                @if($event->location)
                    <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <x-heroicon-s-map-pin class="h-4 w-4 text-blue-400" />
                        {{ $event->location }}
                    </div>
                @endif
                @if($event->url)
                    <a href="{{ $event->url }}" target="_blank" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-purple-600 hover:underline dark:text-purple-400">
                        <x-heroicon-s-link class="h-4 w-4" />
                        Link
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Images --}}
    @if(count($images) > 0)
        <div class="w-full md:w-1/3 min-w-[250px] bg-gray-50 dark:bg-gray-900 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700">
            @if(count($images) === 1)
                <div class="h-48 md:h-full w-full">
                    <img src="{{ $images[0] }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500 cursor-pointer" onclick="window.open('{{ $images[0] }}', '_blank')">
                </div>
            @else
                <div class="h-48 md:h-full w-full grid grid-cols-2 gap-0.5">
                    @foreach(array_slice($images, 0, 4) as $index => $img)
                        <div class="relative w-full h-full overflow-hidden {{ $loop->first && count($images) == 3 ? 'row-span-2' : '' }}">
                            <img src="{{ $img }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500 cursor-pointer" onclick="window.open('{{ $img }}', '_blank')">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Actions --}}
    <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 dark:bg-black/50 rounded-lg p-1 shadow-sm backdrop-blur-sm z-20">
        <button wire:click="editEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}')" class="p-1.5 text-gray-500 hover:text-purple-600 dark:text-gray-300">
            <x-heroicon-o-pencil-square class="h-4 w-4" />
        </button>
        <button wire:click="promptDeleteEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}', {{ $isRepeating ? 'true' : 'false' }})" class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-300">
            <x-heroicon-o-trash class="h-4 w-4" />
        </button>
    </div>
</div>
