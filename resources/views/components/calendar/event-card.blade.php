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
    'canAttend' => false
])

@php
    $groupColor = $event->mixed_color ?? $event->groups->first()->color ?? '#A855F7';
    $isRepeating = $event->repeat_frequency !== 'none';
    $images = $event->images['urls'] ?? [];

    // Permissions Logic
    $user = auth()->user();
    $isCreator = $user && $event->created_by === $user->id;

    // Determine individual capabilities
    $canEdit = $isCreator || $canEditAny;
    $canDelete = $isCreator || $canDeleteAny;

    // Determine if the action container should show at all (fixes "tiny dot" issue)
    $hasActions = $canExport || $canEdit || $canDelete;

    // Badges Generation
    $badges = collect();
    foreach($event->groups as $group) {
        $badges->push([
            'text' => $group->name,
            'style' => "background-color: {$group->color}10; color: {$group->color}; ring-color: {$group->color}20;",
            'classes' => 'ring-1 ring-inset',
        ]);
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

                        {{-- Participants Faces (Always visible for context) --}}
                        <div class="flex items-center -space-x-2 cursor-pointer" wire:click="openParticipantsModal({{ $event->id }})">
                            @foreach($event->participants->where('pivot.status', 'opted_in')->take(3) as $participant)
                                <img src="{{ $participant->profile_picture ? Storage::url($participant->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($participant->username).'&background=random' }}" class="h-6 w-6 rounded-full border-2 border-white dark:border-gray-800" title="{{ $participant->username }}">
                            @endforeach
                            @if($event->participants->where('pivot.status', 'opted_in')->count() > 3)
                                <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-[10px] font-bold text-gray-600 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    +{{ $event->participants->where('pivot.status', 'opted_in')->count() - 3 }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- POLLS SECTION --}}
                @foreach($event->votes as $vote)
                    @php
                        $userHasVoted = $vote->hasUserVoted(auth()->user());
                        $showResults = $userHasVoted;
                        $canSeeBars = $vote->is_public || $event->created_by === auth()->id();
                        $totalVotes = $vote->total_votes;
                    @endphp
                    <div class="mt-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-900/50">
                        <h5 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">{{ $vote->title }} <span class="text-xs font-normal text-gray-500">(Max {{ $vote->max_allowed_selections }})</span></h5>

                        @if($showResults)
                            @if($canSeeBars)
                                <div class="space-y-2">
                                    @foreach($vote->options as $option)
                                        @php
                                            $count = $option->responses->count();
                                            $percent = $totalVotes > 0 ? ($count / $totalVotes) * 100 : 0;
                                        @endphp
                                        <div class="relative h-8 rounded-lg bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="absolute inset-y-0 left-0 bg-purple-200 dark:bg-purple-900/40" style="width: {{ $percent }}%"></div>
                                            <div class="absolute inset-0 flex items-center justify-between px-3">
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 z-10">{{ $option->option_text }}</span>
                                                <span class="text-xs font-bold text-purple-700 dark:text-purple-300 z-10">{{ $count }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-2 text-xs italic text-gray-500">Vote submitted. Results hidden.</div>
                            @endif
                        @else
                            {{-- Voting Form --}}
                            <div class="space-y-2">
                                @foreach($vote->options as $option)
                                    <label wire:key="poll-option-{{ $option->id }}" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-2 hover:border-purple-300 cursor-pointer dark:border-gray-700 dark:bg-gray-800">
                                        <input type="checkbox" wire:model="pollSelections.{{ $vote->id }}.{{ $option->id }}" class="h-4 w-4 rounded text-purple-600 focus:ring-purple-500">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                                <div class="flex justify-end pt-2">
                                    <button wire:click="castVote({{ $vote->id }})" class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-purple-700">Vote</button>
                                </div>
                                @error('poll_'.$vote->id) <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Images (Right Column on Desktop) --}}
        @if(count($images) > 0)
            <div class="w-full md:w-1/3 min-w-[250px] bg-gray-50 dark:bg-gray-900 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700">
                <div class="h-48 md:h-full w-full relative">
                    <img src="{{ $images[0] }}" class="w-full h-full object-cover">
                </div>
            </div>
        @endif

        {{-- Edit/Delete Actions (Hidden if no permission) --}}
        @if($hasActions)
            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 dark:bg-black/50 rounded-lg p-1 shadow-sm backdrop-blur-sm z-20">
                @if($canExport)
                    <button wire:click="openExportModal({{ $event->id }})" class="p-1.5 text-gray-500 hover:text-purple-600" title="Export Event"><x-heroicon-o-arrow-up-on-square class="h-4 w-4" /></button>
                @endif

                @if($canEdit)
                    <button wire:click="editEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}')" class="p-1.5 text-gray-500 hover:text-purple-600"><x-heroicon-o-pencil-square class="h-4 w-4" /></button>
                @endif

                @if($canDelete)
                    <button wire:click="promptDeleteEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}', {{ $isRepeating ? 'true' : 'false' }})" class="p-1.5 text-gray-500 hover:text-red-600"><x-heroicon-o-trash class="h-4 w-4" /></button>
                @endif
            </div>
        @endif
    </div>

    {{-- COMMENTS SECTION (Hidden if no permission) --}}
    @if($event->comments_enabled && $canViewComments)
        <div x-data="{ open: false }" class="border-t border-gray-100 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/50">
            <button @click="open = !open" class="flex w-full items-center justify-between px-6 py-2 text-xs font-bold uppercase tracking-wide text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="flex items-center gap-2"><x-heroicon-o-chat-bubble-left class="h-4 w-4" /> Comments ({{ $event->comments->count() }})</span>
                <x-heroicon-o-chevron-down class="h-3 w-3 transition-transform" ::class="open ? 'rotate-180' : ''" />
            </button>

            <div x-show="open" class="px-6 pb-4 space-y-4">
                {{-- Comment List --}}
                <div class="space-y-3 pt-2">
                    @foreach($event->comments->take($commentLimit) as $comment)
                        <div class="group/comment flex gap-3">
                            <img src="{{ $comment->user->profile_picture ? Storage::url($comment->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($comment->user->username).'&background=random' }}" class="h-6 w-6 rounded-full bg-gray-200 mt-1">
                            <div class="flex-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $comment->user->username }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $comment->content }}</p>

                                <div class="flex items-center gap-2 mt-1">
                                    {{-- Reply Button --}}
                                    @if($canPostComments)
                                        <button wire:click="addReplyMention({{ $event->id }}, '{{ $comment->user->username }}')" class="text-[10px] font-bold text-gray-400 hover:text-purple-600">Reply</button>
                                    @endif

                                    {{-- Delete Button (Own or Permission) --}}
                                    @if(auth()->id() === $comment->user_id || $canDeleteAnyComment)
                                        <button wire:click="deleteComment({{ $comment->id }})"
                                                class="text-gray-400 hover:text-red-600 opacity-0 group-hover/comment:opacity-100 transition-opacity"
                                                title="Delete Comment">
                                            <x-heroicon-o-trash class="h-3 w-3" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($event->comments->count() > $commentLimit)
                        <button wire:click="loadMoreComments({{ $event->id }})" class="w-full py-2 text-xs font-bold text-purple-600 hover:bg-purple-50 rounded-lg dark:hover:bg-purple-900/20">Load more comments</button>
                    @endif
                </div>

                {{-- Comment Input (Hidden if no post permission) --}}
                @if($canPostComments)
                    <div class="flex gap-2">
                        <input type="text"
                               wire:model="commentInputs.{{ $event->id }}"
                               wire:keydown.enter="postComment({{ $event->id }})"
                               placeholder="Write a comment... (Enter to post)"
                               class="flex-1 rounded-lg border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <button wire:click="postComment({{ $event->id }})" class="rounded-lg bg-purple-600 px-3 py-2 text-white hover:bg-purple-700"><x-heroicon-o-paper-airplane class="h-4 w-4" /></button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
