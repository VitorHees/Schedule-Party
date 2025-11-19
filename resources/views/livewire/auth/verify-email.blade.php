<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center">
            <x-app-logo-icon class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" />
        </div>

        {{-- Title --}}
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Verify your email
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            We’ve sent you a verification link. Please check your inbox.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 border border-gray-200 dark:border-gray-700">

            {{-- Status message --}}
            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                    <p class="text-sm text-green-800 dark:text-green-400">
                        A new verification link has been sent to the email address you provided during registration.
                    </p>
                </div>
            @endif

            <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 text-center">
                If you didn’t receive the email, you can request another verification link.
            </p>

            <div class="space-y-4">
                <button
                    type="button"
                    wire:click="sendVerification"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-50 dark:focus:ring-offset-gray-900"
                >
                    Resend verification email
                </button>

                <button
                    type="button"
                    wire:click="logout"
                    class="w-full flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-50 dark:focus:ring-offset-gray-900"
                >
                    Log out
                </button>
            </div>
        </div>
    </div>
</div>
