<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">
    <div class="mx-auto max-w-4xl">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Profile')" :subheading="__('Update your personal information')">
            <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

                {{-- Profile Photo Section --}}
                <div class="flex items-center gap-6">
                    <div class="shrink-0">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-purple-100 dark:ring-purple-900" alt="New Profile Photo">
                        @elseif (auth()->user()->profile_picture)
                            <img src="{{ Storage::url(auth()->user()->profile_picture) }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-800" alt="Current Profile Photo">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-xl font-bold text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                {{ auth()->user()->initials() }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <flux:input wire:model="photo" :label="__('Profile Photo')" type="file" accept="image/*" class="text-sm" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG or GIF (Max 5MB).</p>
                        @error('photo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:input wire:model="username" :label="__('Username')" type="text" required autofocus autocomplete="username" />

                    <div>
                        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                            <div class="mt-2">
                                <flux:text>
                                    {{ __('Your email address is unverified.') }}
                                    <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </flux:text>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </flux:text>
                                @endif
                            </div>
                        @endif
                    </div>

                    <flux:input wire:model="phone_number" :label="__('Phone Number')" type="tel" autocomplete="tel" />

                    <flux:input wire:model="birth_date" :label="__('Birth Date')" type="date" />

                    <flux:select wire:model="gender_id" :label="__('Gender')" placeholder="Select gender">
                        <option value="">{{ __('Select gender') }}</option>
                        @foreach($genders as $gender)
                            <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="country_id" :label="__('Country')" placeholder="Select country">
                        <option value="">{{ __('Select country') }}</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="zipcode_code"
                        :label="__('Zipcode')"
                        type="text"
                        :disabled="empty($country_id)"
                        placeholder="{{ empty($country_id) ? __('Select a country first') : __('e.g. 3900') }}"
                    />
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                    </div>

                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>

            <livewire:settings.delete-user-form />
        </x-settings.layout>
    </div>
</div>
