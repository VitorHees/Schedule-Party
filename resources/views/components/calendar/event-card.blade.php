@props([
    'event',
    'commentLimit' => 3,
    'newComment' => null,
    'pollSelections' => [],
    'canExport' => false,
    'canEditAny' => false,
    'canDeleteAny' => false,
    'canViewComments' => false,
    'canPostComments' => false,
    'canDeleteAnyComment' => false,
    'canAttend' => false,
    'canVote' => false
])

@php
    $groupColor = $event->mixed_color ?? $event->groups->first()->color ?? '#A855F7';
    $isRepeating = $event->repeat_frequency !== 'none';

    // --- DATA PREPARATION ---
    $rawUploads = $event->images['urls'] ?? [];
    $visuals = [];
    $attachments = [];

    // 1. Add MAP as the FIRST visual (if location exists)
    if ($event->latitude && $event->longitude) {
        $delta = 0.004; // Roughly a few city blocks
        $minLon = $event->longitude - $delta;
        $minLat = $event->latitude - $delta;
        $maxLon = $event->longitude + $delta;
        $maxLat = $event->latitude + $delta;

        $visuals[] = [
            'type' => 'map',
            'embedUrl' => "https://www.openstreetmap.org/export/embed.html?bbox={$minLon}%2C{$minLat}%2C{$maxLon}%2C{$maxLat}&layer=mapnik&marker={$event->latitude}%2C{$event->longitude}",
            'linkUrl' => "https://www.openstreetmap.org/?mlat={$event->latitude}&mlon={$event->longitude}#map=17/{$event->latitude}/{$event->longitude}"
        ];
    }

    // 2. Sort Uploads into Visuals (Images) vs Attachments (Files)
    foreach($rawUploads as $upload) {
        $ext = strtolower(pathinfo($upload, PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            $visuals[] = ['type' => 'image', 'url' => $upload];
        } else {
            $attachments[] = ['url' => $upload, 'name' => basename($upload), 'ext' => $ext];
        }
    }

    $hasVisuals = count($visuals) > 0;

    // Permissions Logic
    $user = auth()->user();
    $isCreator = $user && $event->created_by === $user->id;
    $canEdit = $isCreator || $canEditAny;
    $canDelete = $isCreator || $canDeleteAny;
    $hasActions = $canExport || $canEdit || $canDelete;

    // Badges Generation
    $badges = collect();
    foreach($event->groups as $group) {
        $badges->push(['text' => $group->name, 'style' => "background-color: {$group->color}10; color: {$group->color}; ring-color: {$group->color}20;", 'classes' => 'ring-1 ring-inset']);
    }
    if($event->is_nsfw) $badges->push(['text' => 'NSFW', 'classes' => 'border border-red-200 bg-red-50 text-red-600 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400', 'icon' => 'heroicon-s-exclamation-triangle']);
    foreach($event->genders ?? [] as $gender) $badges->push(['text' => $gender->name, 'classes' => 'border border-teal-200 bg-teal-50 text-teal-600 dark:border-teal-800 dark:bg-teal-900/20 dark:text-teal-400', 'icon' => 'heroicon-s-user']);
    if($event->min_age) $badges->push(['text' => $event->min_age . '+', 'classes' => 'border border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400', 'icon' => 'heroicon-s-cake']);
    if($event->max_distance_km) $badges->push(['text' => $event->max_distance_km . 'KM', 'classes' => 'border border-indigo-200 bg-indigo-50 text-indigo-600 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400', 'icon' => 'heroicon-s-map']);
@endphp

<div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:border-purple-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
    <div class="flex flex-col md:flex-row items-stretch">
        {{-- Color Strip --}}
        <div class="absolute left-0 top-0 bottom-0 w-1.5 md:static md:w-1.5 shrink-0" style="background: {{ $groupColor }}"></div>

        {{-- Main Body --}}
        <div class="flex-1 flex flex-col md:flex-row p-6 gap-6">
            {{-- Time Column --}}
            <div class="flex flex-col items-start min-w-[80px]">
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }}</span>
                @if(!$event->is_all_day)
                    <span class="text-xs font-medium text-gray-400">{{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}</span>
                @endif
                @if($isRepeating)
                    <x-heroicon-s-arrow-path class="w-3 h-3 text-gray-400 mt-1" title="Repeating" />
                @endif
            </div>

            {{-- Content Column --}}
            <div class="flex-1 space-y-3">
                {{-- Badges --}}
                <div x-data="{ expanded: false }" class="flex flex-wrap items-center gap-2 mb-1">
                    @foreach($badges as $index => $badge)
                        <span x-show="expanded || {{ $index }} < 3" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badge['classes'] }}" style="{{ $badge['style'] ?? '' }}">
                            @if(isset($badge['icon'])) <x-dynamic-component :component="$badge['icon']" class="h-3 w-3" /> @endif
                            {{ $badge['text'] }}
                        </span>
                    @endforeach
                    @if($badges->count() > 3)
                        <button @click.prevent.stop="expanded = !expanded" class="text-[10px] font-bold text-gray-400 hover:text-purple-600 transition-colors"><span x-show="!expanded">+{{ $badges->count() - 3 }}</span><span x-show="expanded">Less</span></button>
                    @endif
                </div>

                {{-- Title & Description --}}
                <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $event->name }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $event->description }}</p>

                {{-- FILE ATTACHMENTS (PDFs, Zips, etc) --}}
                @if(count($attachments) > 0)
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($attachments as $file)
                            <a href="{{ $file['url'] }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-xs font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200 transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                                <span class="truncate max-w-[150px]">{{ $file['name'] }}</span>
                                <span class="uppercase text-[9px] text-gray-400">{{ $file['ext'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Location / URL --}}
                @if($event->location || $event->url)
                    <div class="mt-2 space-y-1.5 border-t border-gray-100 pt-2 dark:border-gray-700">
                        @if($event->location)
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-s-map-pin class="h-4 w-4 shrink-0 text-gray-400" />
                                <span>{{ $event->location }}</span>
                            </div>
                        @endif
                        @if($event->url)
                            <div class="flex items-center gap-2 text-sm">
                                <x-heroicon-s-link class="h-4 w-4 shrink-0 text-gray-400" />
                                <a href="{{ $event->url }}" target="_blank" rel="noopener noreferrer" class="truncate text-purple-600 hover:underline dark:text-purple-400">
                                    {{ $event->url }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ATTEND SECTION --}}
                @if($event->opt_in_enabled)
                    <div class="flex items-center gap-4 border-t border-gray-100 pt-3 dark:border-gray-700">
                        @if($canAttend)
                            <button wire:click="toggleOptIn({{ $event->id }})" class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors {{ $event->isUserParticipating(auth()->user()) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' }}">
                                @if($event->isUserParticipating(auth()->user()))
                                    <x-heroicon-s-check class="h-4 w-4" /> Attending
                                @else
                                    <x-heroicon-o-plus class="h-4 w-4" /> Attend
                                @endif
                            </button>
                        @endif
                        <div class="flex items-center -space-x-2 cursor-pointer" wire:click="openParticipantsModal({{ $event->id }})">
                            @foreach($event->participants->where('pivot.status', 'opted_in')->take(3) as $participant)
                                <img src="{{ $participant->profile_picture ? Storage::url($participant->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($participant->username).'&background=random' }}" class="h-6 w-6 rounded-full border-2 border-white dark:border-gray-800" title="{{ $participant->username }}">
                            @endforeach
                            @if($event->participants->where('pivot.status', 'opted_in')->count() > 3)
                                <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-[10px] font-bold text-gray-600 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300">+{{ $event->participants->where('pivot.status', 'opted_in')->count() - 3 }}</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- POLLS SECTION --}}
                @foreach($event->votes as $vote)
                    @php
                        $userHasVoted = $vote->hasUserVoted(auth()->user());
                        $showResults = $userHasVoted || !$canVote;
                        $canSeeBars = $vote->is_public || $event->created_by === auth()->id();
                        $totalVotes = $vote->total_votes;
                    @endphp
                    <div class="mt-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-900/50">
                        <h5 class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">{{ $vote->title }} <span class="text-xs font-normal text-gray-500">(Max {{ $vote->max_allowed_selections }})</span></h5>
                        @if($showResults)
                            @if($canSeeBars)
                                <div class="space-y-2">
                                    @foreach($vote->options as $option)
                                        @php $percent = $totalVotes > 0 ? ($option->responses->count() / $totalVotes) * 100 : 0; @endphp
                                        <div class="relative h-8 rounded-lg bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="absolute inset-y-0 left-0 bg-purple-200 dark:bg-purple-900/40" style="width: {{ $percent }}%"></div>
                                            <div class="absolute inset-0 flex items-center justify-between px-3">
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 z-10">{{ $option->option_text }}</span>
                                                <span class="text-xs font-bold text-purple-700 dark:text-purple-300 z-10">{{ $option->responses->count() }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-2 text-xs italic text-gray-500">{{ $userHasVoted ? 'Vote submitted.' : 'Results hidden.' }}</div>
                            @endif
                        @else
                            <div class="space-y-2">
                                @foreach($vote->options as $option)
                                    <label wire:key="poll-option-{{ $option->id }}" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-2 hover:border-purple-300 cursor-pointer dark:border-gray-700 dark:bg-gray-800">
                                        <input type="checkbox" wire:model="pollSelections.{{ $vote->id }}.{{ $option->id }}" class="h-4 w-4 rounded text-purple-600">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                                <div class="flex justify-end pt-2"><button wire:click="castVote({{ $vote->id }})" class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-purple-700">Vote</button></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- VISUAL COLUMN (CAROUSEL) --}}
        @if($hasVisuals)
            <div x-data="{ current: 0, total: {{ count($visuals) }} }" class="w-full md:w-1/3 min-w-[250px] bg-gray-100 dark:bg-gray-900 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700 relative group/visual">

                {{-- Carousel Items --}}
                <div class="h-48 md:h-full w-full relative overflow-hidden">
                    @foreach($visuals as $index => $visual)
                        <div x-show="current === {{ $index }}"
                             class="absolute inset-0 w-full h-full transition-opacity duration-300"
                             x-transition:enter="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="opacity-100"
                             x-transition:leave-end="opacity-0">

                            @if($visual['type'] === 'map')
                                {{-- Map Embed --}}
                                <iframe width="100%" height="100%" frameborder="0" scrolling="no" src="{{ $visual['embedUrl'] }}" class="w-full h-full bg-gray-200 border-0"></iframe>
                                <a href="{{ $visual['linkUrl'] }}" target="_blank" class="absolute top-2 left-2 bg-white/90 p-1 rounded shadow text-xs font-bold text-gray-600 hover:text-purple-600">Open Map</a>
                            @else
                                {{-- Image --}}
                                <img src="{{ $visual['url'] }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Carousel Controls (Only if > 1 item) --}}
                @if(count($visuals) > 1)
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-4 z-10">
                        <button @click="current = (current === 0 ? total - 1 : current - 1)" class="p-1.5 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                            <x-heroicon-s-chevron-left class="w-4 h-4" />
                        </button>
                        <span class="text-xs font-bold text-white shadow-sm flex items-center">
                            <span x-text="current + 1"></span> / {{ count($visuals) }}
                        </span>
                        <button @click="current = (current === total - 1 ? 0 : current + 1)" class="p-1.5 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                            <x-heroicon-s-chevron-right class="w-4 h-4" />
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Actions --}}
        @if($hasActions)
            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-1 shadow-md z-20">
                @if($canExport) <button wire:click="openExportModal({{ $event->id }})" class="p-1.5 text-gray-700 hover:text-purple-700 hover:bg-gray-200 dark:text-gray-300 rounded-md"><x-heroicon-o-arrow-up-on-square class="h-4 w-4" /></button> @endif
                @if($canEdit) <button wire:click="editEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}')" class="p-1.5 text-gray-700 hover:text-purple-700 hover:bg-gray-200 dark:text-gray-300 rounded-md"><x-heroicon-o-pencil-square class="h-4 w-4" /></button> @endif
                @if($canDelete) <button wire:click="promptDeleteEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}', {{ $isRepeating ? 'true' : 'false' }})" class="p-1.5 text-gray-700 hover:text-red-700 hover:bg-red-100 dark:text-gray-300 rounded-md"><x-heroicon-o-trash class="h-4 w-4" /></button> @endif
            </div>
        @endif
    </div>

    {{-- Comments Section (Standard) --}}
    @if($event->comments_enabled && $canViewComments)
        <div x-data="{ open: false }" class="border-t border-gray-100 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/50">
            <button @click="open = !open" class="flex w-full items-center justify-between px-6 py-2 text-xs font-bold uppercase tracking-wide text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="flex items-center gap-2"><x-heroicon-o-chat-bubble-left class="h-4 w-4" /> Comments ({{ $event->comments->count() }})</span>
                <x-heroicon-o-chevron-down class="h-3 w-3 transition-transform" ::class="open ? 'rotate-180' : ''" />
            </button>
            <div x-show="open" class="px-6 pb-4 space-y-4">
                <div class="space-y-3 pt-2">
                    @foreach($event->comments->take($commentLimit) as $comment)
                        <div class="flex gap-3"><div class="flex-1"><p class="text-sm text-gray-600 dark:text-gray-300"><b>{{ $comment->user->username }}:</b> {{ $comment->content }}</p></div></div>
                    @endforeach
                    @if($event->comments->count() > $commentLimit) <button wire:click="loadMoreComments({{ $event->id }})" class="text-xs font-bold text-purple-600">Load more</button> @endif
                </div>
                @if($canPostComments)
                    <div class="flex gap-2"><input type="text" wire:model="commentInputs.{{ $event->id }}" wire:keydown.enter="postComment({{ $event->id }})" class="flex-1 rounded-lg border-gray-200 text-sm"><button wire:click="postComment({{ $event->id }})" class="rounded-lg bg-purple-600 px-3 py-2 text-white"><x-heroicon-o-paper-airplane class="h-4 w-4" /></button></div>
                @endif
            </div>
        </div>
    @endif
</div>
