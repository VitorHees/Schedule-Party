<?php

namespace App\Livewire\Settings;

use App\Models\Country;
use App\Models\Gender;
use App\Models\User;
use App\Models\Zipcode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // Import the HTTP client
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $username = '';
    public string $email = '';
    public string $phone_number = '';
    public string $birth_date = '';
    public ?int $gender_id = null;
    public ?int $country_id = null;
    public string $zipcode_code = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->username = $user->username;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number ?? '';
        $this->birth_date = $user->birth_date ? $user->birth_date->format('Y-m-d') : '';
        $this->gender_id = $user->gender_id;
        $this->country_id = $user->country_id;
        $this->zipcode_code = $user->zipcode?->code ?? '';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender_id' => ['nullable', 'exists:genders,id'],

            // UPDATE THIS RULE:
            'country_id' => ['nullable', 'exists:countries,id', 'required_with:zipcode_code'],

            'zipcode_code' => ['nullable', 'string', 'max:20'],
        ]);

        $zipcodeId = null;

        // Logic: If user entered a zipcode, try to find coordinates for it
        if (!empty($this->zipcode_code)) {

            // 1. Check if we already have this zipcode with coordinates in our DB
            $zipcode = Zipcode::where('code', $this->zipcode_code)->first();

            // 2. If we don't have it (or it's missing coords), fetch from API
            if ((!$zipcode || !$zipcode->latitude) && $this->country_id) {
                $country = Country::find($this->country_id);

                // Fetch from Zippopotam.us (Free, No Key required)
                $coords = $this->fetchCoordinates($country->code, $this->zipcode_code);

                if ($coords) {
                    $zipcode = Zipcode::updateOrCreate(
                        ['code' => $this->zipcode_code],
                        ['latitude' => $coords['lat'], 'longitude' => $coords['lng']]
                    );
                }
            }

            // 3. Fallback: If API failed or no country selected, just create the text entry
            if (!$zipcode) {
                $zipcode = Zipcode::firstOrCreate(['code' => $this->zipcode_code]);
            }

            $zipcodeId = $zipcode->id;
        }

        $user->fill([
            'username' => $this->username,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'birth_date' => $this->birth_date ?: null,
            'gender_id' => $this->gender_id,
            'country_id' => $this->country_id,
            'zipcode_id' => $zipcodeId,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->username);
    }

    /**
     * Helper to get coordinates from the Zippopotam API
     */
    protected function fetchCoordinates(string $countryCode, string $zipcode): ?array
    {
        try {
            // API Format: api.zippopotam.us/{country}/{zipcode}
            $response = Http::timeout(2)->get("https://api.zippopotam.us/" . strtolower($countryCode) . "/" . $zipcode);

            if ($response->successful()) {
                $data = $response->json();

                // Return the first place found
                if (!empty($data['places'][0])) {
                    return [
                        'lat' => $data['places'][0]['latitude'],
                        'lng' => $data['places'][0]['longitude'],
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fail silently and return null if API is down or zipcode invalid
            return null;
        }

        return null;
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    public function render()
    {
        return view('livewire.settings.profile', [
            'genders' => Gender::all(),
            'countries' => Country::all(),
        ]);
    }
}
