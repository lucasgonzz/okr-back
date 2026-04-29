<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Empresa base sobre la cual se crean los departamentos del mock frontend.
         */
        $company = Company::where('slug', 'demo')->first();

        if (! $company) {
            return;
        }

        /**
         * Departamentos mock trasladados desde OKR/frontend/lib/mock-data.ts.
         */
        $departments = [
            ['name' => 'Comercial', 'slug' => 'comercial', 'color' => '#10B981'],
            ['name' => 'IT', 'slug' => 'it', 'color' => '#6366F1'],
            ['name' => 'Producto', 'slug' => 'producto', 'color' => '#F59E0B'],
            ['name' => 'Admin', 'slug' => 'admin', 'color' => '#8B5CF6'],
            ['name' => 'Business Operations', 'slug' => 'business-operations', 'color' => '#EC4899'],
            ['name' => 'Performance', 'slug' => 'performance', 'color' => '#14B8A6'],
            ['name' => 'Chile', 'slug' => 'chile', 'color' => '#EF4444'],
            ['name' => 'Perú', 'slug' => 'peru', 'color' => '#F97316'],
            ['name' => 'Europa', 'slug' => 'europa', 'color' => '#3B82F6'],
        ];

        foreach ($departments as $department) {
            /**
             * Payload final persistido por cada departamento para la empresa demo.
             */
            $department_data = [
                'company_id' => $company->id,
                'name' => $department['name'],
                'slug' => $department['slug'],
                'color' => $department['color'],
            ];

            Department::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'slug' => $department['slug'],
                ],
                $department_data
            );
        }
    }
}
