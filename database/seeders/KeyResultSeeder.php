<?php

namespace Database\Seeders;

use App\Http\Controllers\Helpers\KeyResultHelper;
use App\Http\Controllers\Helpers\ObjectiveHelper;
use App\Models\KeyResult;
use App\Models\Objective;
use App\Models\User;
use Illuminate\Database\Seeder;

class KeyResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Mapa de objetivos por título para asociar cada KR a su objetivo padre.
         */
        $objective_by_title = Objective::all()->keyBy('title');

        /**
         * Mapa de usuarios por email para resolver owner_id de cada KR.
         */
        $user_by_email = User::all()->keyBy('email');

        /**
         * Key Results completos del mock frontend agrupados por objetivo.
         */
        $key_results_by_objective = [
            'Incrementar Ingresos Q4 en 25%' => [
                ['title' => 'Cerrar 50 nuevos contratos enterprise', 'owner_email' => 'carlos.rodriguez@company.com', 'target' => 50, 'current_value' => 34, 'start_value' => 0, 'unit' => ' contratos', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => 'Buen avance en el pipeline de Q4'],
                ['title' => 'Alcanzar $2M en revenue de upsell', 'owner_email' => 'javier.diaz@company.com', 'target' => 2000000, 'current_value' => 1500000, 'start_value' => 0, 'unit' => '', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Reducir ciclo de ventas a 45 días', 'owner_email' => 'carlos.rodriguez@company.com', 'target' => 45, 'current_value' => 52, 'start_value' => 60, 'unit' => ' días', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => 'Proceso de aprobación interno lento en algunos clientes', 'comments' => 'Necesitamos revisar el proceso de onboarding'],
                ['title' => 'Incrementar valor del pipeline en 40%', 'owner_email' => 'javier.diaz@company.com', 'target' => 40, 'current_value' => 34, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
            ],
            'Expandir a 3 Nuevos Segmentos de Mercado' => [
                ['title' => 'Completar investigación de mercado para 5 segmentos', 'owner_email' => 'javier.diaz@company.com', 'target' => 5, 'current_value' => 4, 'start_value' => 0, 'unit' => ' reportes', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Lanzar pilotos en 3 segmentos', 'owner_email' => 'javier.diaz@company.com', 'target' => 3, 'current_value' => 1, 'start_value' => 0, 'unit' => ' pilotos', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Falta definición de propuesta de valor para segmento fintech', 'comments' => ''],
                ['title' => 'Lograr $500K revenue de nuevos segmentos', 'owner_email' => 'carlos.rodriguez@company.com', 'target' => 500000, 'current_value' => 100000, 'start_value' => 0, 'unit' => '', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Ciclos de venta más largos de lo esperado', 'comments' => ''],
            ],
            'Alcanzar 99.9% de Uptime de Plataforma' => [
                ['title' => 'Reducir MTTR a menos de 15 minutos', 'owner_email' => 'ana.martinez@company.com', 'target' => 15, 'current_value' => 12, 'start_value' => 45, 'unit' => ' min', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => 'Objetivo superado'],
                ['title' => 'Implementar failover automático para servicios críticos', 'owner_email' => 'ana.martinez@company.com', 'target' => 100, 'current_value' => 75, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Desplegar dashboards de monitoreo para todos los servicios', 'owner_email' => 'ana.martinez@company.com', 'target' => 12, 'current_value' => 12, 'start_value' => 0, 'unit' => ' dashboards', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => 'Completado exitosamente'],
                ['title' => 'Completar pruebas de disaster recovery', 'owner_email' => 'ana.martinez@company.com', 'target' => 3, 'current_value' => 2, 'start_value' => 0, 'unit' => ' pruebas', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
            ],
            'Migrar 80% de Servicios a la Nube' => [
                ['title' => 'Migrar 10 aplicaciones críticas', 'owner_email' => 'ana.martinez@company.com', 'target' => 10, 'current_value' => 7, 'start_value' => 0, 'unit' => ' apps', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Reducir costos de infraestructura en 30%', 'owner_email' => 'ana.martinez@company.com', 'target' => 30, 'current_value' => 15, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Costos de licencias cloud más altos de lo presupuestado', 'comments' => ''],
                ['title' => 'Capacitar a 50 ingenieros en tecnologías cloud', 'owner_email' => 'ana.martinez@company.com', 'target' => 50, 'current_value' => 40, 'start_value' => 0, 'unit' => ' ingenieros', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
            ],
            'Lanzar App Móvil v2.0' => [
                ['title' => 'Completar desarrollo de nuevas funcionalidades', 'owner_email' => 'pedro.lopez@company.com', 'target' => 100, 'current_value' => 55, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Retrasos en definición de UX para flujo de checkout', 'comments' => ''],
                ['title' => 'Lograr rating 4.5+ en app stores (beta)', 'owner_email' => 'pedro.lopez@company.com', 'target' => 4.5, 'current_value' => 4.2, 'start_value' => 3.8, 'unit' => ' estrellas', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Reducir tiempo de carga a menos de 2 segundos', 'owner_email' => 'pedro.lopez@company.com', 'target' => 2, 'current_value' => 2.8, 'start_value' => 4.5, 'unit' => ' seg', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Optimización de imágenes pendiente', 'comments' => ''],
                ['title' => 'Completar auditoría y correcciones de accesibilidad', 'owner_email' => 'pedro.lopez@company.com', 'target' => 100, 'current_value' => 30, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Falta de recursos especializados en a11y', 'comments' => 'Solicitar apoyo externo'],
            ],
            'Mejorar Retención de Usuarios en 15%' => [
                ['title' => 'Lanzar motor de recomendaciones personalizadas', 'owner_email' => 'pedro.lopez@company.com', 'target' => 100, 'current_value' => 100, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => 'Mostrando resultados positivos'],
                ['title' => 'Implementar optimización de notificaciones push', 'owner_email' => 'pedro.lopez@company.com', 'target' => 100, 'current_value' => 65, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Reducir churn rate Day-30 a 25%', 'owner_email' => 'pedro.lopez@company.com', 'target' => 25, 'current_value' => 28, 'start_value' => 35, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
            ],
            'Reducir Costos Operativos en 20%' => [
                ['title' => 'Renegociar 10 contratos principales con proveedores', 'owner_email' => 'maria.garcia@company.com', 'target' => 10, 'current_value' => 9, 'start_value' => 0, 'unit' => ' contratos', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Automatizar 5 procesos manuales', 'owner_email' => 'maria.garcia@company.com', 'target' => 5, 'current_value' => 4, 'start_value' => 0, 'unit' => ' procesos', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Reducir costos de espacio de oficina en 15%', 'owner_email' => 'maria.garcia@company.com', 'target' => 15, 'current_value' => 10, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => false, 'blockers' => '', 'comments' => 'Este KR no suma al objetivo por decisión estratégica'],
            ],
            'Implementar Sistema OKR Company-Wide' => [
                ['title' => 'Capacitar a todos los jefes de departamento en OKR', 'owner_email' => 'laura.sanchez@company.com', 'target' => 9, 'current_value' => 9, 'start_value' => 0, 'unit' => ' jefes', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Lograr 100% de participación de departamentos', 'owner_email' => 'laura.sanchez@company.com', 'target' => 9, 'current_value' => 9, 'start_value' => 0, 'unit' => ' deptos', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Establecer cadencia de check-ins semanales', 'owner_email' => 'laura.sanchez@company.com', 'target' => 100, 'current_value' => 100, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
            ],
            'Lanzar Sistema de Gestión de Desempeño' => [
                ['title' => 'Seleccionar e implementar software de performance', 'owner_email' => 'diego.fernandez@company.com', 'target' => 100, 'current_value' => 100, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Completar sesiones de capacitación para managers', 'owner_email' => 'diego.fernandez@company.com', 'target' => 25, 'current_value' => 20, 'start_value' => 0, 'unit' => ' sesiones', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Lograr 90% de onboarding de empleados', 'owner_email' => 'diego.fernandez@company.com', 'target' => 90, 'current_value' => 54, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Resistencia al cambio en algunos equipos', 'comments' => ''],
            ],
            'Crecer Market Share Chile a 25%' => [
                ['title' => 'Establecer 5 partnerships estratégicos', 'owner_email' => 'sofia.ruiz@company.com', 'target' => 5, 'current_value' => 3, 'start_value' => 0, 'unit' => ' socios', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Negociaciones lentas con partner clave', 'comments' => ''],
                ['title' => 'Lanzar 3 campañas de marketing localizadas', 'owner_email' => 'sofia.ruiz@company.com', 'target' => 3, 'current_value' => 2, 'start_value' => 0, 'unit' => ' campañas', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Abrir 2 nuevas oficinas regionales', 'owner_email' => 'sofia.ruiz@company.com', 'target' => 2, 'current_value' => 1, 'start_value' => 0, 'unit' => ' oficinas', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Demoras en permisos municipales', 'comments' => ''],
                ['title' => 'Contratar 20 representantes de ventas locales', 'owner_email' => 'sofia.ruiz@company.com', 'target' => 20, 'current_value' => 9, 'start_value' => 0, 'unit' => ' reps', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Competencia agresiva por talento', 'comments' => ''],
            ],
            'Establecer Excelencia Operativa en Perú' => [
                ['title' => 'Completar construcción oficina Lima', 'owner_email' => 'alejandro.torres@company.com', 'target' => 100, 'current_value' => 85, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Contratar y capacitar equipo de operaciones de 15', 'owner_email' => 'alejandro.torres@company.com', 'target' => 15, 'current_value' => 11, 'start_value' => 0, 'unit' => ' personas', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Lograr compliance regulatorio local', 'owner_email' => 'alejandro.torres@company.com', 'target' => 100, 'current_value' => 50, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Nuevos requerimientos regulatorios identificados', 'comments' => ''],
            ],
            'Lanzar Expansión Mercado EU' => [
                ['title' => 'Lograr compliance GDPR completo', 'owner_email' => 'isabella.moreno@company.com', 'target' => 100, 'current_value' => 100, 'start_value' => 0, 'unit' => '%', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Lanzar en Alemania, Francia y España', 'owner_email' => 'isabella.moreno@company.com', 'target' => 3, 'current_value' => 2, 'start_value' => 0, 'unit' => ' países', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Establecer equipo de soporte EU', 'owner_email' => 'isabella.moreno@company.com', 'target' => 10, 'current_value' => 6, 'start_value' => 0, 'unit' => ' agentes', 'status' => KeyResult::STATUS_EMPTY, 'sums_to_objective' => true, 'blockers' => '', 'comments' => ''],
                ['title' => 'Firmar 20 clientes enterprise EU', 'owner_email' => 'isabella.moreno@company.com', 'target' => 20, 'current_value' => 11, 'start_value' => 0, 'unit' => ' clientes', 'status' => KeyResult::STATUS_AT_RISK, 'sums_to_objective' => true, 'blockers' => 'Competencia local fuerte en Francia', 'comments' => ''],
            ],
        ];

        foreach ($key_results_by_objective as $objective_title => $key_results) {
            /**
             * Objetivo padre donde se insertarán los key results de este bloque.
             */
            $objective = $objective_by_title->get($objective_title);

            if (! $objective) {
                continue;
            }

            foreach ($key_results as $key_result) {
                /**
                 * Usuario owner del key result.
                 */
                $owner = $user_by_email->get($key_result['owner_email']);

                if (! $owner) {
                    continue;
                }

                /**
                 * Payload base para crear o actualizar el key result.
                 */
                $key_result_data = [
                    'objective_id' => $objective->id,
                    'owner_id' => $owner->id,
                    'title' => $key_result['title'],
                    'description' => 'Key result for tracking ' . mb_strtolower($key_result['title']),
                    'target' => $key_result['target'],
                    'current_value' => $key_result['current_value'],
                    'start_value' => $key_result['start_value'],
                    'unit' => $key_result['unit'],
                    'status' => $key_result['status'],
                    'sums_to_objective' => $key_result['sums_to_objective'],
                    'blockers' => $key_result['blockers'] !== '' ? $key_result['blockers'] : null,
                    'comments' => $key_result['comments'] !== '' ? $key_result['comments'] : null,
                ];

                /**
                 * Registro persistido del key result para luego recalcular progreso y estado.
                 */
                $persisted_key_result = KeyResult::updateOrCreate(
                    [
                        'objective_id' => $objective->id,
                        'title' => $key_result['title'],
                    ],
                    $key_result_data
                );

                /**
                 * Recalculo de campos derivados según reglas de negocio del backend.
                 */
                KeyResultHelper::recalcProgressAndStatus($persisted_key_result);

                /**
                 * En KRs marcados manualmente como riesgo, mantenemos ese estado explícito.
                 */
                if ($key_result['status'] === KeyResult::STATUS_AT_RISK) {
                    $persisted_key_result->status = KeyResult::STATUS_AT_RISK;
                }

                $persisted_key_result->save();
            }

            /**
             * Recalcula el objetivo completo luego de insertar/actualizar todos sus KRs.
             */
            ObjectiveHelper::recalcFromKeyResults($objective->fresh());
        }
    }
}
