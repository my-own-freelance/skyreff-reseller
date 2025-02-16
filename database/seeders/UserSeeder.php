<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "code" => "ADMSUPEROK",
            "name" => "superadmin",
            "username" => "superadmin",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "ADMIN"
        ]);

        User::create([
            "code" => "ADMCOREOKE",
            "name" => "core admin",
            "username" => "coreadmin",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "ADMIN"
        ]);

        User::create([
            "code" => "RESREDHAAF",
            "name" => "Redha",
            "username" => "redha",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "RESELLER",
            "level" => "VIP"
        ]);

        User::create([
            "code" => "RESAKHARIS",
            "name" => "Kharis",
            "username" => "kharis",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "RESELLER",
            "level" => "REGULAR"
        ]);
    }
}
