<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">
    <div class="mx-auto max-w-4xl">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
            <form wire:submit="updatePassword" class="mt-6 w-full space-y-6">

                <flux:input wire:model="current_password" :label="__('Current Password')" type="password" required autocomplete="current-password" />
                <flux:input wire:model="password" :label="__('New Password')" type="password" required autocomplete="new-password" />
                <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required autocomplete="new-password" />

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                    </div>

                    <x-action-message class="me-3" on="password-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>
        </x-settings.layout>
    </div>
</div>
