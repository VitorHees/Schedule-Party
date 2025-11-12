<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700">
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
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">
                            Log In
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition">
                            Get Started
                        </a>
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
            <div x-data="{ mobileMenuOpen: false }" x-show="mobileMenuOpen" x-cloak class="md:hidden py-4 border-t border-gray-200 dark:border-gray-700">
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
                        <a href="{{ route('register') }}" class="px-8 py-4 text-lg font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                            Start Free Trial
                        </a>
                        <a href="#how-it-works" class="px-8 py-4 text-lg font-semibold text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border-2 border-purple-600 rounded-lg transition">
                            Learn More
                        </a>
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

                {{-- Hero Image / Calendar Preview --}}
                <div class="relative" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 300)">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-white dark:bg-gray-800 p-6"
                         x-show="loaded"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0">
                        {{-- Mock Calendar --}}
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">December 2025</h3>
                                <div class="flex gap-2">
                                    <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
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

                                @for($i = 1; $i <= 35; $i++)
                                    @php
                                        $dayNumber = $i - 3;
                                        $isCurrentMonth = $dayNumber > 0 && $dayNumber <= 31;
                                        $hasEvent = in_array($dayNumber, [5, 12, 18, 25]);
                                    @endphp
                                    <div class="aspect-square flex items-center justify-center rounded-lg {{ $isCurrentMonth ? 'bg-gray-50 dark:bg-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20' : 'bg-transparent' }} {{ $hasEvent ? 'ring-2 ring-purple-500' : '' }} cursor-pointer transition">
                                        @if($isCurrentMonth)
                                            <span class="text-sm {{ $hasEvent ? 'font-bold text-purple-600 dark:text-purple-400' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $dayNumber }}
                                            </span>
                                        @endif
                                    </div>
                                @endfor
                            </div>

                            {{-- Upcoming Events Preview --}}
                            <div class="mt-6 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Upcoming Events</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                                        <div class="w-2 h-2 rounded-full bg-purple-600"></div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Team Meeting</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Dec 5, 2:00 PM</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Project Deadline</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Dec 12, 5:00 PM</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                {{-- Feature 1 --}}
                <div class="p-6 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-lg bg-purple-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Collaborative Calendars</h3>
                    <p class="text-gray-600 dark:text-gray-400">Share calendars with teams, friends, and family. Control who can view and edit.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-6 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-lg bg-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Smart Notifications</h3>
                    <p class="text-gray-600 dark:text-gray-400">Never miss an event with customizable reminders and real-time updates.</p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-6 rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-lg bg-green-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mobile Ready</h3>
                    <p class="text-gray-600 dark:text-gray-400">Access your schedule anywhere, anytime on any device with our responsive design.</p>
                </div>

                {{-- Feature 4 --}}
                <div class="p-6 rounded-xl bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-lg bg-yellow-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Privacy First</h3>
                    <p class="text-gray-600 dark:text-gray-400">Your data is encrypted and secure. You control who sees what.</p>
                </div>

                {{-- Feature 5 --}}
                <div class="p-6 rounded-xl bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-900/20 dark:to-pink-800/20 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-lg bg-pink-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Custom Themes</h3>
                    <p class="text-gray-600 dark:text-gray-400">Personalize your calendar with custom colors, themes, and dark mode.</p>
                </div>

                {{-- Feature 6 --}}
                <div class="p-6 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 hover:shadow-lg transition">
                    <div class="w-12 h-12 rounded-lg bg-indigo-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Analytics & Insights</h3>
                    <p class="text-gray-600 dark:text-gray-400">Track your time, see patterns, and optimize your schedule.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works Section --}}
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
                {{-- Step 1 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-purple-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Create Your Account</h3>
                    <p class="text-gray-600 dark:text-gray-400">Sign up in seconds and get your personal calendar ready to go.</p>

                    {{-- Connector Line --}}
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gradient-to-r from-purple-600 to-blue-600 -z-10"></div>
                </div>

                {{-- Step 2 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Add Events & Share</h3>
                    <p class="text-gray-600 dark:text-gray-400">Create events and invite others to collaborate on shared calendars.</p>

                    {{-- Connector Line --}}
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gradient-to-r from-blue-600 to-green-600 -z-10"></div>
                </div>

                {{-- Step 3 --}}
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-green-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Stay Organized</h3>
                    <p class="text-gray-600 dark:text-gray-400">Get notifications, sync across devices, and never miss a beat.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Simple, Transparent Pricing
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    Choose the plan that works for you
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Free Plan --}}
                <div class="p-8 rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-600 dark:hover:border-purple-400 transition">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Free</h3>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        $0<span class="text-lg text-gray-600 dark:text-gray-400">/month</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Perfect for personal use</p>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">1 Personal Calendar</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Unlimited Events</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Mobile Access</span>
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" class="block w-full py-3 text-center font-semibold text-purple-600 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 rounded-lg transition">
                        Get Started
                    </a>
                </div>

                {{-- Pro Plan (Featured) --}}
                <div class="p-8 rounded-2xl bg-gradient-to-br from-purple-600 to-blue-600 text-white relative transform md:scale-105 shadow-2xl">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-purple-900 px-4 py-1 rounded-full text-sm font-bold">
                        POPULAR
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Pro</h3>
                    <div class="text-4xl font-bold mb-4">
                        $9<span class="text-lg opacity-80">/month</span>
                    </div>
                    <p class="opacity-90 mb-6">For power users and teams</p>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Unlimited Calendars</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Collaborative Sharing</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Priority Support</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Advanced Analytics</span>
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" class="block w-full py-3 text-center font-semibold text-purple-600 bg-white hover:bg-gray-100 rounded-lg transition">
                        Start Free Trial
                    </a>
                </div>

                {{-- Enterprise Plan --}}
                <div class="p-8 rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-600 dark:hover:border-purple-400 transition">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Enterprise</h3>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Custom
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">For large organizations</p>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Everything in Pro</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Dedicated Support</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">SSO & Security</span>
                        </li>
                    </ul>

                    <a href="mailto:[email protected]" class="block w-full py-3 text-center font-semibold text-purple-600 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 rounded-lg transition">
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            <div class="p-12 rounded-3xl bg-gradient-to-br from-purple-600 to-blue-600 text-white">
                <h2 class="text-4xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-xl mb-8 opacity-90">Join thousands of users who are scheduling smarter every day.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="px-8 py-4 text-lg font-semibold text-purple-600 bg-white hover:bg-gray-100 rounded-lg transition">
                        Start Free Trial
                    </a>
                    <a href="#features" class="px-8 py-4 text-lg font-semibold text-white border-2 border-white hover:bg-white/10 rounded-lg transition">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xl font-bold">Schedule Party</span>
                    </div>
                    <p class="text-gray-400 text-sm">Making scheduling fun and collaborative for everyone.</p>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Roadmap</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">About</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Careers</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Security</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-gray-800 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Schedule Party. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
