<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('location/districts.json'));
        $districts = json_decode($json, true);

        foreach ($districts as $district) {
            $provinceId = Province::where('key', substr($district['id'], 0, 2))->value('id');

            District::create([
                'key' => $district['id'],
                'province_id' => $provinceId,
                'name' => $district['nama'],
            ]);
        }
    }
}
