<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center">
            <x-app-logo-icon class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" />
        </div>

        {{-- Title --}}
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Two-Factor Authentication
        </h2>
        <div x-data="{ recovery: false }">
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400" x-show="!recovery">
                Please enter the authentication code provided by your authenticator application.
            </p>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400" x-show="recovery" style="display: none;">
                Please enter one of your emergency recovery codes.
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md"
         x-data="{
            recovery: false,
            toggle() {
                this.recovery = !this.recovery;
                $nextTick(() => {
                    if(this.recovery) { $refs.recovery_code.focus() }
                    else { $refs.code.focus() }
                });
            }
         }">

        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl shadow-purple-900/5 sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700">
            <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-6">
                @csrf

                {{-- Auth Code Input --}}
                <div x-show="!recovery">
                    <label for="code" class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Authentication Code
                    </label>
                    <div class="mt-1">
                        <input
                            x-ref="code"
                            id="code"
                            type="text"
                            inputmode="numeric"
                            name="code"
                            autofocus
                            autocomplete="one-time-code"
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all text-center tracking-widest font-mono text-lg"
                            placeholder="000000"
                        >
                    </div>
                    @error('code')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recovery Code Input --}}
                <div x-show="recovery" style="display: none;">
                    <label for="recovery_code" class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Recovery Code
                    </label>
                    <div class="mt-1">
                        <input
                            x-ref="recovery_code"
                            id="recovery_code"
                            type="text"
                            name="recovery_code"
                            autocomplete="one-time-code"
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-transparent sm:text-sm transition-all"
                            placeholder="Recovery Code"
                        >
                    </div>
                    @error('recovery_code')
                    <p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-gray-800 transition-all hover:-translate-y-0.5"
                    >
                        Log in
                    </button>
                </div>
            </form>

            {{-- Toggle Link --}}
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium">
                            Having trouble?
                        </span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <button
                        type="button"
                        @click="toggle()"
                        class="text-sm font-bold text-purple-600 hover:text-purple-500 dark:text-purple-400 dark:hover:text-purple-300 focus:outline-none"
                    >
                        <span x-show="!recovery">Use a recovery code</span>
                        <span x-show="recovery" style="display: none;">Use an authentication code</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
