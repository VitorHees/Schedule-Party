<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    {{-- Main Auth Card --}}
    <div class="w-full max-w-md" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
        <x-personal.auth-card>
            {{-- Logo and Title --}}
            <div class="text-center mb-8"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex justify-center mb-4">
                    <x-app-logo-icon class="size-16 fill-current text-purple-600 dark:text-purple-400" />
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Welcome Back
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Log in to your Schedule Party account
                </p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status class="mb-6" :status="session('status')" />

            {{-- Login Form --}}
            <form wire:submit="login" class="space-y-6"
                  x-show="loaded"
                  x-transition:enter="transition ease-out duration-500 delay-100"
                  x-transition:enter-start="opacity-0 translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0">

                {{-- Email Field --}}
                <div>
                    <flux:input
                        wire:model="email"
                        :label="__('Email address')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="[email protected]"
                    />
                </div>

                {{-- Password Field --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <flux:label>{{ __('Password') }}</flux:label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium transition"
                               wire:navigate>
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>
                    <flux:input
                        wire:model="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Enter your password')"
                        viewable
                    />
                </div>

                {{-- Remember Me --}}
                <div>
                    <flux:checkbox wire:model="remember" :label="__('Remember me for 30 days')" />
                </div>

                {{-- Submit Button --}}
                <x-personal.button type="submit" class="w-full py-3 text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    {{ __('Log In') }}
                </x-personal.button>
            </form>

            {{-- Sign Up Link --}}
            @if (Route::has('register'))
                <div class="mt-6 text-center"
                     x-show="loaded"
                     x-transition:enter="transition ease-out duration-500 delay-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Don't have an account?
                        <a href="{{ route('register') }}"
                           class="font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition"
                           wire:navigate>
                            Sign up for free
                        </a>
                    </p>
                </div>
            @endif
        </x-personal.auth-card>

        {{-- Decorative Elements (matching homepage) --}}
        <div class="absolute -z-10 top-20 -right-20 w-72 h-72 bg-purple-200 dark:bg-purple-600/30 rounded-full blur-3xl opacity-40 dark:opacity-50"></div>
        <div class="absolute -z-10 bottom-20 -left-20 w-72 h-72 bg-blue-200 dark:bg-blue-600/30 rounded-full blur-3xl opacity-40 dark:opacity-50"></div>
    </div>
</div>
