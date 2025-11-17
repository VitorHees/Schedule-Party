<?php

namespace Database\Seeders;

use App\Models\Zipcode;
use Illuminate\Database\Seeder;

class ZipcodeSeeder extends Seeder
{
    public function run(): void
    {
        $zipcodes = [
            // Belgium
            ['code' => '2300', 'latitude' => 51.2194, 'longitude' => 4.4025], // Turnhout
            ['code' => '2000', 'latitude' => 51.2213, 'longitude' => 4.3997], // Antwerp
            ['code' => '1000', 'latitude' => 50.8503, 'longitude' => 4.3517], // Brussels
            ['code' => '9000', 'latitude' => 51.0543, 'longitude' => 3.7174], // Ghent
            ['code' => '3000', 'latitude' => 50.8798, 'longitude' => 4.7005], // Leuven
            ['code' => '8000', 'latitude' => 51.2093, 'longitude' => 3.2247], // Bruges

            // Netherlands
            ['code' => '1012', 'latitude' => 52.3676, 'longitude' => 4.9041], // Amsterdam
            ['code' => '3011', 'latitude' => 51.9225, 'longitude' => 4.4792], // Rotterdam
        ];

        foreach ($zipcodes as $zipcode) {
            Zipcode::create($zipcode);
        }
    }
}
