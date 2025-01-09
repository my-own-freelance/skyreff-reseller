<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\SubDistrict;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubDistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('location/sub_districts.json'));
        $subDistricts = json_decode($json, true);

        foreach ($subDistricts as $subDistrict) {
            $districtId = District::where('key', substr($subDistrict['id'], 0, 4))->value('id');

            SubDistrict::create([
                'key' => $subDistrict['id'],
                'district_id' => $districtId,
                'name' => $subDistrict['nama'],
            ]);
        }
    }
}
