<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'owner@schoola.test'],
            ['name' => 'School Admin', 'password' => Hash::make('password123')]
        );
        $admin->assignRole('school_admin');
    }
}
