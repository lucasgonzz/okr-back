<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Empresa demo donde viven los usuarios trasladados desde el mock del frontend.
         */
        $company = Company::where('slug', 'demo')->first();

        if (! $company) {
            return;
        }

        /**
         * Mapa para resolver cada departamento por slug y asociar usuarios por foreign key.
         */
        $department_by_slug = Department::where('company_id', $company->id)
            ->get()
            ->keyBy('slug');

        /**
         * Usuarios mock completos del frontend.
         */
        $users = [
            ['name' => 'María García', 'email' => 'maria.garcia@company.com', 'role' => User::ROLE_ADMIN, 'department_slug' => 'admin', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Maria'],
            ['name' => 'Carlos Rodríguez', 'email' => 'carlos.rodriguez@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'comercial', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Carlos'],
            ['name' => 'Ana Martínez', 'email' => 'ana.martinez@company.com', 'role' => User::ROLE_ADMIN, 'department_slug' => 'it', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Ana'],
            ['name' => 'Pedro López', 'email' => 'pedro.lopez@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'producto', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Pedro'],
            ['name' => 'Laura Sánchez', 'email' => 'laura.sanchez@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'business-operations', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Laura'],
            ['name' => 'Diego Fernández', 'email' => 'diego.fernandez@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'performance', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Diego'],
            ['name' => 'Sofía Ruiz', 'email' => 'sofia.ruiz@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'chile', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Sofia'],
            ['name' => 'Alejandro Torres', 'email' => 'alejandro.torres@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'peru', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Alejandro'],
            ['name' => 'Isabella Moreno', 'email' => 'isabella.moreno@company.com', 'role' => User::ROLE_ADMIN, 'department_slug' => 'europa', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Isabella'],
            ['name' => 'Javier Díaz', 'email' => 'javier.diaz@company.com', 'role' => User::ROLE_USER, 'department_slug' => 'comercial', 'avatar' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Javier'],
        ];

        foreach ($users as $user) {
            /**
             * Departamento al que pertenece el usuario.
             */
            $department = $department_by_slug->get($user['department_slug']);

            /**
             * Datos finales persistidos para cada usuario mock.
             */
            $user_data = [
                'company_id' => $company->id,
                'department_id' => $department ? $department->id : null,
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('123456'),
                'role' => $user['role'],
                'avatar' => $user['avatar'],
            ];

            User::updateOrCreate(
                ['email' => $user['email']],
                $user_data
            );
        }
    }
}
