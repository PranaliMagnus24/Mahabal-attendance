<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        echo "Admin user created successfully with phone number: 1234567890 and password: 12345678\n";
    }
}