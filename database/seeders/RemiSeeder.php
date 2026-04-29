<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Remi;
use App\Models\User;
use Illuminate\Database\Seeder;

class RemiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Empresa demo para acotar los REMIs del dataset mock.
         */
        $company = Company::where('slug', 'demo')->first();

        if (! $company) {
            return;
        }

        /**
         * Usuarios indexados por email para resolver responsables del REMI.
         */
        $user_by_email = User::where('company_id', $company->id)
            ->get()
            ->keyBy('email');

        /**
         * Departamentos indexados por slug para resolver responsible_department_id.
         */
        $department_by_slug = Department::where('company_id', $company->id)
            ->get()
            ->keyBy('slug');

        /**
         * REMIs trasladados desde OKR/frontend/lib/mock-data.ts.
         */
        $remis = [
            [
                'name' => 'Expansión de Ingresos LATAM',
                'description' => 'Consolidar y expandir nuestra presencia en mercados latinoamericanos, aumentando la participación de mercado y diversificando fuentes de ingreso.',
                'responsible_email' => 'carlos.rodriguez@company.com',
                'responsible_department_slug' => 'comercial',
            ],
            [
                'name' => 'Excelencia Tecnológica',
                'description' => 'Modernizar la infraestructura tecnológica y garantizar una plataforma confiable, escalable y segura para soportar el crecimiento del negocio.',
                'responsible_email' => 'ana.martinez@company.com',
                'responsible_department_slug' => 'it',
            ],
            [
                'name' => 'Experiencia de Usuario Superior',
                'description' => 'Ofrecer una experiencia de producto excepcional que incremente la retención, satisfacción y engagement de nuestros usuarios.',
                'responsible_email' => 'pedro.lopez@company.com',
                'responsible_department_slug' => 'producto',
            ],
        ];

        foreach ($remis as $remi) {
            /**
             * Usuario responsable del REMI.
             */
            $responsible_user = $user_by_email->get($remi['responsible_email']);

            /**
             * Departamento responsable del REMI.
             */
            $responsible_department = $department_by_slug->get($remi['responsible_department_slug']);

            /**
             * Payload final para persistir el REMI dentro de la empresa demo.
             */
            $remi_data = [
                'company_id' => $company->id,
                'name' => $remi['name'],
                'description' => $remi['description'],
                'responsible_user_id' => $responsible_user ? $responsible_user->id : null,
                'responsible_department_id' => $responsible_department ? $responsible_department->id : null,
            ];

            Remi::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $remi['name'],
                ],
                $remi_data
            );
        }
    }
}
