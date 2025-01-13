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
        // User::create([
        //     "code" => "sa12345",
        //     "name" => "superadmin",
        //     "username" => "superadmin",
        //     "password" => Hash::make("rahasia"),
        //     "is_active" => "Y",
        //     "role" => "ADMIN"
        // ]);

        User::create([
            "code" => "re12345",
            "name" => "reseller",
            "username" => "reseller",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "RESELLER"
        ]);
    }
}
