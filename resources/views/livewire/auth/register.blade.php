<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center">
            <x-app-logo-icon class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" />
        </div>

        {{-- Title --}}
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Get started with Schedule Party
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl shadow-purple-900/5 sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700">
            <form wire:submit="register" class="space-y-6">
                {{-- Name / Username --}}
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Username
                    </label>
                    <div class="mt-1">
                        <input
                            wire:model="name"
                            id="name"
                            type="text"
                            required
                            autofocus
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all"
                            placeholder="johndoe"
                        >
                    </div>
                    @error('name')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input
                            wire:model="email"
                            id="email"
                            type="email"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all"
                            placeholder="email@example.com"
                        >
                    </div>
                    @error('email')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Password
                    </label>
                    <div class="mt-1">
                        <input
                            wire:model="password"
                            id="password"
                            type="password"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                    @error('password')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Confirm password
                    </label>
                    <div class="mt-1">
                        <input
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            type="password"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                    @error('password_confirmation')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-gray-800 transition-all hover:-translate-y-0.5"
                    >
                        Create account
                    </button>
                </div>
            </form>

            {{-- Login Link --}}
            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium">
                            Already have an account?
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <a
                        href="{{ route('login') }}"
                        wire:navigate
                        class="w-full flex justify-center py-3 px-4 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                    >
                        Sign in instead
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
