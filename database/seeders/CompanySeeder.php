<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Listado de empresas demo que representan la data base del entorno OKR.
         */
        $companies = [
            [
                'name' => 'Demo Corp',
                'slug' => 'demo',
                'active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['slug' => $company['slug']],
                $company
            );
        }
    }
}
