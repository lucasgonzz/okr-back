<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Quarter;
use Illuminate\Database\Seeder;

class QuarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Empresa demo para crear los 4 cuatrimestres por año.
         */
        $company = Company::where('slug', 'demo')->first();

        if (! $company) {
            return;
        }

        /**
         * Cuatrimestres disponibles para el mock actual.
         */
        $quarters = [
            ['name' => 'Q1-2024', 'year' => 2024, 'position' => 1],
            ['name' => 'Q2-2024', 'year' => 2024, 'position' => 2],
            ['name' => 'Q3-2024', 'year' => 2024, 'position' => 3],
            ['name' => 'Q4-2024', 'year' => 2024, 'position' => 4],
            ['name' => 'Q1-2025', 'year' => 2025, 'position' => 1],
            ['name' => 'Q2-2025', 'year' => 2025, 'position' => 2],
            ['name' => 'Q3-2025', 'year' => 2025, 'position' => 3],
            ['name' => 'Q4-2025', 'year' => 2025, 'position' => 4],
        ];

        foreach ($quarters as $quarter) {
            /**
             * Datos finales para persistir cada cuatrimestre.
             */
            $quarter_data = [
                'company_id' => $company->id,
                'name' => $quarter['name'],
                'year' => $quarter['year'],
                'position' => $quarter['position'],
            ];

            Quarter::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $quarter['name'],
                ],
                $quarter_data
            );
        }
    }
}
