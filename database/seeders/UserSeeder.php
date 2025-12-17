<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create main demo users
        $users = [
            [
                'username' => 'john_doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+32478123456',
                'birth_date' => '1990-05-15',
                'country_id' => 1, // Belgium
                'zipcode_id' => 1, // 2300 Turnhout
                'gender_id' => 1, // Male
                'email_verified_at' => now(),
            ],
            [
                'username' => 'sarah_smith',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+32478234567',
                'birth_date' => '1992-08-22',
                'country_id' => 1, // Belgium
                'zipcode_id' => 2, // 2000 Antwerp
                'gender_id' => 2, // Female
                'email_verified_at' => now(),
            ],
            [
                'username' => 'mike_johnson',
                'email' => 'mike@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+32478345678',
                'birth_date' => '1988-12-10',
                'country_id' => 1, // Belgium
                'zipcode_id' => 3, // 1000 Brussels
                'gender_id' => 1, // Male
                'email_verified_at' => now(),
            ],
            [
                'username' => 'emily_davis',
                'email' => 'emily@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+31612345678',
                'birth_date' => '1995-03-18',
                'country_id' => 2, // Netherlands
                'zipcode_id' => 7, // Amsterdam
                'gender_id' => 2, // Female
                'email_verified_at' => now(),
            ],
            [
                'username' => 'alex_brown',
                'email' => 'alex@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+32478456789',
                'birth_date' => '1993-07-25',
                'country_id' => 1, // Belgium
                'zipcode_id' => 4, // 9000 Ghent
                'gender_id' => 3, // Non-binary
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Output success message
        $this->command->info('Created 5 demo users (password: "password" for all)');
    }
}
