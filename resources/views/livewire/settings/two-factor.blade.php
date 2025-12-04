<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">
    <div class="mx-auto max-w-4xl">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Two-factor Authentication')" :subheading="__('Add additional security to your account using two-factor authentication.')">

            {{-- Main Content Area --}}
            <div class="mt-6 space-y-6">

                {{-- Status Card --}}
                <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">
                            @if ($this->twoFactorEnabled)
                                {{ __('You have enabled two-factor authentication.') }}
                            @else
                                {{ __('You have not enabled two-factor authentication.') }}
                            @endif
                        </h3>
                        <p class="mt-2 max-w-xl text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                        </p>
                    </div>
                </div>

                {{-- Action Area --}}
                @if ($this->twoFactorEnabled)
                    <div class="space-y-6">
                        {{-- Recovery Codes --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900/50">
                            <div class="sm:flex sm:items-center sm:justify-between">
                                <div class="mb-4 sm:mb-0">
                                    <h4 class="text-base font-bold text-zinc-900 dark:text-white">{{ __('Recovery Codes') }}</h4>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Store these codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.') }}</p>
                                </div>
                                <flux:button wire:click="regenerateRecoveryCodes" size="sm">{{ __('Regenerate') }}</flux:button>
                            </div>

                            {{-- Recovery Codes List (Only show if we have user accessible method or hard refresh) --}}
                            <div class="mt-4 grid gap-4 sm:grid-cols-2 font-mono text-sm">
                                @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                    <div class="rounded-md bg-white p-3 text-center border border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300">
                                        {{ $code }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Disable Button --}}
                        <div class="flex justify-end">
                            <flux:button variant="danger" wire:click="disable">{{ __('Disable Two-Factor Authentication') }}</flux:button>
                        </div>
                    </div>
                @else
                    <div class="flex justify-end">
                        <flux:button variant="primary" wire:click="enable">{{ __('Enable') }}</flux:button>
                    </div>
                @endif
            </div>

            {{-- Setup Modal (Flux or Native) --}}
            @if($showModal)
                <flux:modal name="two-factor-modal" class="min-w-[400px]" :show="$showModal" wire:model.live="showModal">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ $this->modalConfig['title'] }}</flux:heading>
                            <flux:subheading>{{ $this->modalConfig['description'] }}</flux:subheading>
                        </div>

                        @if(!$this->twoFactorEnabled || $showVerificationStep)
                            {{-- QR Code & Key --}}
                            @if($qrCodeSvg)
                                <div class="flex flex-col items-center justify-center space-y-4 rounded-lg bg-white p-4">
                                    <div class="h-48 w-48 text-center bg-white p-2">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Setup Key</p>
                                        <p class="font-mono text-sm text-zinc-900">{{ $manualSetupKey }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Code Input --}}
                            <div class="space-y-2">
                                <flux:input wire:model="code" label="{{ __('Code') }}" placeholder="123456" class="text-center font-mono text-lg tracking-widest" />
                                @error('code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="flex justify-end gap-2">
                            <flux:button variant="ghost" wire:click="closeModal">{{ __('Cancel') }}</flux:button>

                            @if($this->twoFactorEnabled && !$showVerificationStep)
                                <flux:button variant="primary" wire:click="closeModal">{{ __('Done') }}</flux:button>
                            @elseif($showVerificationStep)
                                <flux:button variant="primary" wire:click="confirmTwoFactor">{{ __('Confirm') }}</flux:button>
                            @else
                                <flux:button variant="primary" wire:click="showVerificationIfNecessary">{{ __('Next') }}</flux:button>
                            @endif
                        </div>
                    </div>
                </flux:modal>
            @endif

        </x-settings.layout>
    </div>
</div>
