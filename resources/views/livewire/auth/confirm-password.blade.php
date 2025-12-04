<x-layouts.auth>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">

        {{-- Branding --}}
        <div class="mb-8 flex flex-col items-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-purple-100 p-4 dark:bg-purple-900/20">
                <x-app-logo-icon class="h-10 w-10 text-purple-600 dark:text-purple-400" />
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ __('Confirm Password') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>
        </div>

        {{-- Card --}}
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="p-8">
                {{-- Standard HTML Form for Fortify --}}
                <form method="POST" action="/user/confirm-password">
                    @csrf

                    <div class="space-y-6">
                        {{-- Changed wire:model to name="password" --}}
                        <flux:input
                            name="password"
                            label="{{ __('Password') }}"
                            type="password"
                            required
                            autofocus
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />

                        <flux:button variant="primary" type="submit" class="w-full py-3 shadow-lg hover:shadow-purple-500/20">
                            {{ __('Confirm') }}
                        </flux:button>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 px-8 py-4 text-center dark:bg-gray-900/50">
                <flux:link href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    {{ __('Cancel and return to dashboard') }}
                </flux:link>
            </div>
        </div>
    </div>
</x-layouts.auth>
