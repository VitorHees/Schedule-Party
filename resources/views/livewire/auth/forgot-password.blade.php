<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w/full sm:max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center">
            <x-app-logo-icon class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" />
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
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 border border-gray-200 dark:border-gray-700">

            {{-- Session Status --}}
            <x-auth-session-status class="mb-6 text-center" :status="session('status')" />

            <form method="POST" wire:submit="sendPasswordResetLink" class="space-y-6">
                @csrf

                {{-- Email Address --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
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
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>
                    @error('email')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-50 dark:focus:ring-offset-gray-900"
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
                    class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 ml-1"
                >
                    log in
                </a>
            </div>
        </div>
    </div>
</div>
