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
        $superadmin = User::create([
            "code" => "sa12345",
            "name" => "superadmin",
            "username" => "superadmin",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "ADMIN"
        ]);
    }
}
