<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@gmail.com'
            ],
            [
                'name' => 'Super Admin',
                'phone' => '9876543210',
                'password' => Hash::make('12345678'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'created_by' => User::CREATED_BY_SELF,
            ]
        );
    }
}