<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Objective;
use App\Models\Quarter;
use App\Models\Remi;
use App\Models\User;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Empresa demo sobre la cual se carga el set de objetivos de prueba.
         */
        $company = Company::where('slug', 'demo')->first();

        if (! $company) {
            return;
        }

        /**
         * Mapa de departamentos por slug para resolver la relación de cada objetivo.
         */
        $department_by_slug = Department::where('company_id', $company->id)
            ->get()
            ->keyBy('slug');

        /**
         * Mapa de usuarios por email para resolver owners.
         */
        $user_by_email = User::where('company_id', $company->id)
            ->get()
            ->keyBy('email');

        /**
         * Mapa de REMIs por nombre para resolver remi_id.
         */
        $remi_by_name = Remi::where('company_id', $company->id)
            ->get()
            ->keyBy('name');

        /**
         * Mapa de cuatrimestres por nombre para resolver quarter_id.
         */
        $quarter_by_name = Quarter::where('company_id', $company->id)
            ->get()
            ->keyBy('name');

        /**
         * Objetivos mock del frontend con su configuración completa.
         */
        $objectives = [
            ['title' => 'Incrementar Ingresos Q4 en 25%', 'description' => 'Impulsar un crecimiento significativo de ingresos a través de la adquisición de nuevos clientes y upselling de cuentas existentes.', 'department_slug' => 'comercial', 'owner_email' => 'carlos.rodriguez@company.com', 'remi_name' => 'Expansión de Ingresos LATAM', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Expandir a 3 Nuevos Segmentos de Mercado', 'description' => 'Identificar y penetrar tres nuevos segmentos de mercado para diversificar fuentes de ingreso.', 'department_slug' => 'comercial', 'owner_email' => 'javier.diaz@company.com', 'remi_name' => 'Expansión de Ingresos LATAM', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_AT_RISK],
            ['title' => 'Alcanzar 99.9% de Uptime de Plataforma', 'description' => 'Garantizar una confiabilidad excepcional de la plataforma a través de mejoras de infraestructura y monitoreo proactivo.', 'department_slug' => 'it', 'owner_email' => 'ana.martinez@company.com', 'remi_name' => 'Excelencia Tecnológica', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Migrar 80% de Servicios a la Nube', 'description' => 'Acelerar la adopción de cloud migrando servicios legacy on-premise a infraestructura cloud moderna.', 'department_slug' => 'it', 'owner_email' => 'ana.martinez@company.com', 'remi_name' => 'Excelencia Tecnológica', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Lanzar App Móvil v2.0', 'description' => 'Entregar una experiencia móvil completamente rediseñada con nuevas funcionalidades y mejor rendimiento.', 'department_slug' => 'producto', 'owner_email' => 'pedro.lopez@company.com', 'remi_name' => 'Experiencia de Usuario Superior', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_AT_RISK],
            ['title' => 'Mejorar Retención de Usuarios en 15%', 'description' => 'Implementar funcionalidades y mejoras que impulsen el engagement y reduzcan el churn.', 'department_slug' => 'producto', 'owner_email' => 'pedro.lopez@company.com', 'remi_name' => 'Experiencia de Usuario Superior', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Reducir Costos Operativos en 20%', 'description' => 'Optimizar procesos administrativos y renegociar contratos con proveedores para lograr ahorros.', 'department_slug' => 'admin', 'owner_email' => 'maria.garcia@company.com', 'remi_name' => null, 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_MANUAL, 'manual_progress' => 78, 'manual_status' => Objective::STATUS_EMPTY, 'progress' => 78, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Implementar Sistema OKR Company-Wide', 'description' => 'Desplegar y lograr adopción completa de metodología OKR en todos los departamentos.', 'department_slug' => 'business-operations', 'owner_email' => 'laura.sanchez@company.com', 'remi_name' => null, 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_MANUAL, 'manual_progress' => 100, 'manual_status' => Objective::STATUS_COMPLETED, 'progress' => 100, 'status' => Objective::STATUS_COMPLETED],
            ['title' => 'Lanzar Sistema de Gestión de Desempeño', 'description' => 'Implementar un sistema moderno de gestión de desempeño con feedback continuo.', 'department_slug' => 'performance', 'owner_email' => 'diego.fernandez@company.com', 'remi_name' => null, 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Crecer Market Share Chile a 25%', 'description' => 'Expandir presencia en el mercado chileno a través de partnerships estratégicos y marketing localizado.', 'department_slug' => 'chile', 'owner_email' => 'sofia.ruiz@company.com', 'remi_name' => 'Expansión de Ingresos LATAM', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_AT_RISK],
            ['title' => 'Establecer Excelencia Operativa en Perú', 'description' => 'Construir un equipo de operaciones de clase mundial e infraestructura en Perú.', 'department_slug' => 'peru', 'owner_email' => 'alejandro.torres@company.com', 'remi_name' => 'Expansión de Ingresos LATAM', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
            ['title' => 'Lanzar Expansión Mercado EU', 'description' => 'Entrar y establecer presencia exitosa en mercados europeos clave con ofertas localizadas.', 'department_slug' => 'europa', 'owner_email' => 'isabella.moreno@company.com', 'remi_name' => 'Expansión de Ingresos LATAM', 'quarter' => 'Q4-2024', 'calculation_mode' => Objective::CALC_AUTOMATIC, 'manual_progress' => null, 'manual_status' => null, 'progress' => 0, 'status' => Objective::STATUS_EMPTY],
        ];

        foreach ($objectives as $objective) {
            /**
             * Departamento asociado al objetivo.
             */
            $department = $department_by_slug->get($objective['department_slug']);

            /**
             * Owner del objetivo.
             */
            $owner = $user_by_email->get($objective['owner_email']);

            /**
             * REMI asociado al objetivo (si corresponde).
             */
            $remi = $objective['remi_name'] ? $remi_by_name->get($objective['remi_name']) : null;
            /**
             * Cuatrimestre asociado al objetivo.
             */
            $quarter = $quarter_by_name->get($objective['quarter']);

            if (! $department || ! $owner) {
                continue;
            }

            /**
             * Payload final persistido para cada objetivo.
             */
            $objective_data = [
                'company_id' => $company->id,
                'department_id' => $department->id,
                'owner_id' => $owner->id,
                'remi_id' => $remi ? $remi->id : null,
                'quarter_id' => $quarter ? $quarter->id : null,
                'title' => $objective['title'],
                'description' => $objective['description'],
                'quarter' => $objective['quarter'],
                'calculation_mode' => $objective['calculation_mode'],
                'manual_progress' => $objective['manual_progress'],
                'manual_status' => $objective['manual_status'],
                'progress' => $objective['progress'],
                'status' => $objective['status'],
            ];

            Objective::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'title' => $objective['title'],
                ],
                $objective_data
            );
        }
    }
}
