<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Belgium', 'code' => 'BEL'],
            ['name' => 'Netherlands', 'code' => 'NLD'],
            ['name' => 'Germany', 'code' => 'DEU'],
            ['name' => 'France', 'code' => 'FRA'],
            ['name' => 'United Kingdom', 'code' => 'GBR'],
            ['name' => 'United States', 'code' => 'USA'],
            ['name' => 'Canada', 'code' => 'CAN'],
            ['name' => 'Spain', 'code' => 'ESP'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
