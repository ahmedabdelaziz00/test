<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ahmed Ali',    'email' => 'ahmed@example.com'],
            ['name' => 'Sara Mohamed', 'email' => 'sara@example.com'],
            ['name' => 'Omar Hassan',  'email' => 'omar@example.com'],
            ['name' => 'Nour Khaled',  'email' => 'nour@example.com'],
            ['name' => 'Youssef Adel', 'email' => 'youssef@example.com'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name'     => $user['name'],
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
