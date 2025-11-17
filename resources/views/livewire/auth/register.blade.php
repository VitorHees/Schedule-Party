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
                    Create Your Account
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Start scheduling smarter today
                </p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status class="mb-6" :status="session('status')" />

            {{-- Register Form --}}
            <form wire:submit="register" class="space-y-5"
                  x-show="loaded"
                  x-transition:enter="transition ease-out duration-500 delay-100"
                  x-transition:enter-start="opacity-0 translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0">

                {{-- Name Field --}}
                <div>
                    <flux:input
                        wire:model="name"
                        :label="__('Full Name')"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        :placeholder="__('John Doe')"
                    />
                </div>

                {{-- Email Field --}}
                <div>
                    <flux:input
                        wire:model="email"
                        :label="__('Email address')"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="[email protected]"
                    />
                </div>

                {{-- Password Field --}}
                <div>
                    <flux:input
                        wire:model="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Create a strong password')"
                        viewable
                    />
                </div>

                {{-- Confirm Password Field --}}
                <div>
                    <flux:input
                        wire:model="password_confirmation"
                        :label="__('Confirm Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Re-enter your password')"
                        viewable
                    />
                </div>

                {{-- Terms & Conditions (optional, you can add this later) --}}
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    By creating an account, you agree to our
                    <a href="#" class="text-purple-600 dark:text-purple-400 hover:underline">Terms of Service</a>
                    and
                    <a href="#" class="text-purple-600 dark:text-purple-400 hover:underline">Privacy Policy</a>.
                </div>

                {{-- Submit Button --}}
                <x-personal.button type="submit" class="w-full py-3 text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    {{ __('Create Account') }}
                </x-personal.button>
            </form>

            {{-- Login Link --}}
            <div class="mt-6 text-center"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Already have an account?
                    <a href="{{ route('login') }}"
                       class="font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition"
                       wire:navigate>
                        Log in
                    </a>
                </p>
            </div>
        </x-personal.auth-card>

        {{-- Decorative Elements (matching homepage) --}}
        <div class="absolute -z-10 top-20 -right-20 w-72 h-72 bg-purple-200 dark:bg-purple-600/30 rounded-full blur-3xl opacity-40 dark:opacity-50"></div>
        <div class="absolute -z-10 bottom-20 -left-20 w-72 h-72 bg-blue-200 dark:bg-blue-600/30 rounded-full blur-3xl opacity-40 dark:opacity-50"></div>
    </div>
</div>
