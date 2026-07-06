<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table.
     */
    public function run(): void
    {
        // ユーザー1（一般）
        User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'email_verified_at' => now(),
            'admin_status' => false,
            'password' => Hash::make('password'),
        ]);

        // ユーザー2（一般）
        User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'email_verified_at' => now(),
            'admin_status' => false,
            'password' => Hash::make('password'),
        ]);

        // ユーザー3（管理者）
        User::create([
            'name' => 'ユーザー3',
            'email' => 'user3@example.com',
            'email_verified_at' => now(),
            'admin_status' => true,
            'password' => Hash::make('password'),
        ]);
    }
}