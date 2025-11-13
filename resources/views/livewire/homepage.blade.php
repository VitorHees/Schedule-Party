<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    {{-- Navigation --}}
    <nav x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Schedule Party
                    </span>
                </div>

                {{-- Navigation Links --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">Features</a>
                    <a href="#how-it-works" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">How It Works</a>
                    <a href="#pricing" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">Pricing</a>
                </div>

                {{-- Auth Buttons --}}
                <div class="flex items-center space-x-4">
                    @auth
                        <x-personal.button href="{{ route('dashboard') }}">Dashboard</x-personal.button>
                    @else
                        <x-personal.button variant="ghost" href="{{ route('login') }}">Log In</x-personal.button>
                        <x-personal.button href="{{ route('register') }}">Get Started</x-personal.button>
                    @endauth

                    {{-- Mobile Menu Button --}}
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-col space-y-4">
                    <a href="#features" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">Features</a>
                    <a href="#how-it-works" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">How It Works</a>
                    <a href="#pricing" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">Pricing</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Hero Content --}}
                <div class="text-center lg:text-left" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6"
                        x-show="loaded"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 -translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Schedule Smarter, <br>
                        <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                            Party Better
                        </span>
                    </h1>

                    <p class="text-xl text-gray-600 dark:text-gray-400 mb-8"
                       x-show="loaded"
                       x-transition:enter="transition ease-out duration-700 delay-150"
                       x-transition:enter-start="opacity-0 -translate-y-4"
                       x-transition:enter-end="opacity-100 translate-y-0">
                        The ultimate collaborative calendar app for teams, friends, and families.
                        Plan events, share schedules, and never miss a moment.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start"
                         x-show="loaded"
                         x-transition:enter="transition ease-out duration-700 delay-300"
                         x-transition:enter-start="opacity-0 -translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <x-personal.button href="{{ route('register') }}" class="px-8 py-4 text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Start Free Trial
                        </x-personal.button>
                        <x-personal.button variant="secondary" href="#how-it-works" class="px-8 py-4 text-lg">
                            Learn More
                        </x-personal.button>
                    </div>

                    {{-- Stats --}}
                    <div class="mt-12 grid grid-cols-3 gap-6"
                         x-show="loaded"
                         x-transition:enter="transition ease-out duration-700 delay-500"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">10K+</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Active Users</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">50K+</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Events Created</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">99%</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Satisfaction</div>
                        </div>
                    </div>
                </div>

                {{-- Calendar Preview (Interactive) --}}
                <div class="relative" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 300)">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-white dark:bg-gray-800 p-6"
                         x-show="loaded"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0">

                        {{-- Calendar Header --}}
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y') }}
                            </h3>
                            <div class="flex gap-2">
                                <button wire:click="prevMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Previous month">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button wire:click="nextMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Next month">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Calendar Grid --}}
                        <div class="grid grid-cols-7 gap-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="text-center text-xs font-semibold text-gray-600 dark:text-gray-400 py-2">
                                    {{ $day }}
                                </div>
                            @endforeach

                            @php
                                $monthKey = sprintf('%04d-%02d', $this->year, $this->month);
                            @endphp

                            @foreach($this->grid as $date)
                                @php
                                    $isCurrentMonth = (int) $date->month === (int) $this->month;
                                    $isSelected = $date->toDateString() === $this->selectedDate;
                                    $isToday = $date->isToday();
                                    $hasEvent = isset($this->eventsMap[$monthKey][$date->toDateString()]);
                                @endphp

                                <button
                                    wire:click="selectDay('{{ $date->toDateString() }}')"
                                    wire:key="day-{{ $date->toDateString() }}"
                                    class="aspect-square flex items-center justify-center rounded-lg transition
                                           {{ $isCurrentMonth ? 'bg-gray-50 dark:bg-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20' : 'bg-transparent text-gray-400' }}
                                           {{ $hasEvent ? 'ring-2 ring-purple-500' : '' }}
                                           {{ $isSelected ? 'bg-gray-300 dark:bg-gray-600' : '' }}"
                                    title="{{ $date->toFormattedDateString() }}"
                                    aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                >
                                    <span class="text-sm
                                               {{ $isCurrentMonth ? 'text-gray-700 dark:text-gray-200' : 'text-gray-400' }}
                                               {{ $hasEvent ? 'font-semibold text-purple-600 dark:text-purple-400' : '' }}
                                               {{ $isSelected ? 'font-bold text-gray-900 dark:text-white' : '' }}">
                                        {{ $date->day }}
                                    </span>
                                    @if($isToday)
                                        <span class="sr-only">(today)</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        {{-- Events for Selected Day --}}
                        <div class="mt-6">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Events for {{ \Carbon\Carbon::parse($this->selectedDate)->format('M j, Y') }}
                            </h4>

                            @if(count($this->selectedEvents))
                                <div class="mt-2 space-y-2">
                                    @foreach($this->selectedEvents as $ev)
                                        <x-personal.event-item
                                            :title="$ev['title']"
                                            :start="$ev['start']"
                                            :end="$ev['end']"
                                            :color="$ev['color']"
                                        />
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    No events for this day.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Decorative Elements --}}
                    <div class="absolute -z-10 top-10 -right-10 w-72 h-72 bg-purple-300 dark:bg-purple-600/30 rounded-full blur-3xl opacity-50"></div>
                    <div class="absolute -z-10 -bottom-10 -left-10 w-72 h-72 bg-blue-300 dark:bg-blue-600/30 rounded-full blur-3xl opacity-50"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Everything You Need to Schedule Better
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    Powerful features designed for collaboration and productivity
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <x-personal.feature-card title="Collaborative Calendars" preset="purple">
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 013-3h1a3 3 0 013 3" />
                        </svg>
                    </x-slot:icon>
                    Share calendars with teams, friends, and family. Control who can view and edit.
                </x-personal.feature-card>

                <x-personal.feature-card title="Smart Notifications" preset="blue">
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.34" />
                        </svg>
                    </x-slot:icon>
                    Never miss an event with customizable reminders and real-time updates.
                </x-personal.feature-card>

                <x-personal.feature-card title="Mobile Ready" preset="green">
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                    Access your schedule anywhere, anytime on any device with our responsive design.
                </x-personal.feature-card>

                <x-personal.feature-card title="Privacy First" preset="yellow">
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04" />
                        </svg>
                    </x-slot:icon>
                    Your data is encrypted and secure. You control who sees what.
                </x-personal.feature-card>

                <x-personal.feature-card title="Custom Themes" preset="pink">
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2H9" />
                        </svg>
                    </x-slot:icon>
                    Personalize your calendar with custom colors, themes, and dark mode.
                </x-personal.feature-card>

                <x-personal.feature-card title="Analytics & Insights" preset="indigo">
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2" />
                        </svg>
                    </x-slot:icon>
                    Track your time, see patterns, and optimize your schedule.
                </x-personal.feature-card>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section id="how-it-works" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How Schedule Party Works
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    Get started in three simple steps
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-purple-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Create Your Account</h3>
                    <p class="text-gray-600 dark:text-gray-400">Sign up in seconds and get your personal calendar ready to go.</p>
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gradient-to-r from-purple-600 to-blue-600 -z-10"></div>
                </div>

                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Add Events & Share</h3>
                    <p class="text-gray-600 dark:text-gray-400">Create events and invite others to collaborate on shared calendars.</p>
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gradient-to-r from-blue-600 to-green-600 -z-10"></div>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-green-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Stay Organized</h3>
                    <p class="text-gray-600 dark:text-gray-400">Get notifications, sync across devices, and never miss a beat.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Simple, Transparent Pricing</h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">Choose the plan that works for you</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <x-personal.card
                    title="Free"
                    :price="0"
                    ctaLabel="Get Started"
                    ctaHref="{{ route('register') }}"
                    :perks="['1 Personal Calendar', 'Unlimited Events', 'Mobile Access']"
                />

                <x-personal.card
                    title="Pro"
                    :price="9"
                    featured
                    ctaLabel="Start Free Trial"
                    ctaHref="{{ route('register') }}"
                    :perks="['Unlimited Calendars', 'Collaborative Sharing', 'Priority Support', 'Advanced Analytics']"
                />

                <x-personal.card
                    title="Enterprise"
                    :price="null"
                    ctaLabel="Contact Sales"
                    ctaHref="mailto:[email protected]"
                    :perks="['Everything in Pro', 'Dedicated Support', 'SSO & Security']"
                />
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            <div class="p-12 rounded-3xl bg-gradient-to-br from-purple-600 to-blue-600 text-white">
                <h2 class="text-4xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-xl mb-8 opacity-90">Join thousands of users who are scheduling smarter every day.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <x-personal.button href="{{ route('register') }}" class="px-8 py-4 text-lg bg-white text-purple-600 hover:bg-gray-100">
                        Start Free Trial
                    </x-personal.button>
                    <x-personal.button variant="ghost" href="#features" class="px-8 py-4 text-lg text-white border-2 border-white hover:bg-white/10">
                        Learn More
                    </x-personal.button>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <x-personal.footer />
</div>
