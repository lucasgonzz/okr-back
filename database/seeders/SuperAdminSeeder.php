<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'superadmin@platform.com'],
            [
                'company_id' => null,
                'department_id' => null,
                'name' => 'Super Admin',
                'password' => Hash::make('123456'),
                'role' => User::ROLE_SUPER_ADMIN,
            ]
        );
    }
}
