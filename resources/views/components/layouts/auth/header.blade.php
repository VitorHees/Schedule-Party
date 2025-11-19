<nav
    x-data="{
        // Mobile menu state
        mobileMenuOpen: false,

        // Flux appearance: 'light' | 'dark' | 'system'
        appearance: window.localStorage.getItem('flux.appearance') || 'system',

        // Computed dark mode for the toggle UI
        get darkMode() {
            return this.appearance === 'dark';
        },
        set darkMode(val) {
            this.appearance = val ? 'dark' : 'light';
        },
    }"
    x-init="
        if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
            // Apply stored or system appearance on load
            window.Flux.applyAppearance(appearance || 'system');
        }

        // Whenever appearance changes, forward it to Flux
        $watch('appearance', value => {
            if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
                window.Flux.applyAppearance(value);
            }
        });
    "
    class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center space-x-2" wire:navigate>
                <x-app-logo-icon class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" />
                <span class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                    Schedule Party
                </span>
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center space-x-8">
                @guest
                    <a href="{{ route('home') }}#features" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">
                        Features
                    </a>
                    <a href="{{ route('home') }}#how-it-works" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">
                        How It Works
                    </a>
                    <a href="{{ route('home') }}#pricing" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition">
                        Pricing
                    </a>
                @endguest
            </div>

            {{-- Right Side: Dark Mode Toggle + Auth Buttons --}}
            <div class="flex items-center space-x-4">
                {{-- Dark Mode Toggle --}}
                <button
                    @click="darkMode = !darkMode"
                    class="p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    aria-label="Toggle dark mode"
                >
                    {{-- Sun Icon (when currently dark) --}}
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>

                    {{-- Moon Icon (when currently light) --}}
                    <svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 transition font-medium"
                       wire:navigate>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden sm:inline-block px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition font-medium"
                       wire:navigate>
                        Log In
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 transition font-medium"
                       wire:navigate>
                        Get Started
                    </a>
                @endauth

                {{-- Mobile Menu Button --}}
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            @click.away="mobileMenuOpen = false"
            class="md:hidden py-4 border-t border-gray-200 dark:border-gray-700"
        >
            <div class="flex flex-col space-y-4">
                @guest
                    <a href="{{ route('home') }}#features"
                       class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition"
                       @click="mobileMenuOpen = false">
                        Features
                    </a>
                    <a href="{{ route('home') }}#how-it-works"
                       class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition"
                       @click="mobileMenuOpen = false">
                        How It Works
                    </a>
                    <a href="{{ route('home') }}#pricing"
                       class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition"
                       @click="mobileMenuOpen = false">
                        Pricing
                    </a>
                    <a href="{{ route('login') }}"
                       class="sm:hidden text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition"
                       wire:navigate
                       @click="mobileMenuOpen = false">
                        Log In
                    </a>
                @endguest
            </div>
        </div>
    </div>
</nav>
