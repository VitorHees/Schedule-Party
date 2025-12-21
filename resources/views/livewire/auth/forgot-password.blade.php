<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center">
            <x-app-logo-icon class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" />
        </div>

        {{-- Title --}}
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Forgot password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Enter your email to receive a password reset link
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl shadow-purple-900/5 sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700">

            {{-- Session Status --}}
            <x-auth-session-status class="mb-6 text-center" :status="session('status')" />

            <form method="POST" wire:submit="sendPasswordResetLink" class="space-y-6">
                @csrf

                {{-- Email Address --}}
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
                            autofocus
                            placeholder="email@example.com"
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all"
                        >
                    </div>
                    @error('email')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-gray-800 transition-all hover:-translate-y-0.5"
                    >
                        Email password reset link
                    </button>
                </div>
            </form>

            {{-- Back to login --}}
            <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <span>Or, return to</span>
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="font-bold text-purple-600 dark:text-purple-400 hover:text-purple-500 dark:hover:text-purple-300 ml-1"
                >
                    log in
                </a>
            </div>
        </div>
    </div>
</div>
