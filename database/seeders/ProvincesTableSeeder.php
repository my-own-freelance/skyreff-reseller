<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvincesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('location/provinces.json'));
        $provinces = json_decode($json, true);

        foreach ($provinces as $province) {
            Province::create([
                'name' => $province['nama'],
                'key' => $province['id'],
                'timeZone' => $province['timeZone'],
            ]);
        }
    }
}
