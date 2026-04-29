<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            SuperAdminSeeder::class,
            CompanySeeder::class,
            QuarterSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            RemiSeeder::class,
            ObjectiveSeeder::class,
            KeyResultSeeder::class,
        ]);
    }
}
