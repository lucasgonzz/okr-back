<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Controllers\Helpers\ObjectiveHelper;
use App\Http\Resources\ObjectiveResource;
use App\Models\Objective;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Http\Request;

class ObjectiveController extends Controller
{
    private const LOG_FIELDS = [
        'title' => 'Titulo',
        'description' => 'Descripcion',
        'department_id' => 'Departamento',
        'owner_id' => 'Responsable',
        'remi_id' => 'Remi',
        'quarter_id' => 'Cuatrimestre',
        'quarter' => 'Nombre del cuatrimestre',
        'calculation_mode' => 'Modo de calculo',
        'manual_progress' => 'Progreso manual',
        'manual_status' => 'Estado manual',
        'progress' => 'Progreso',
        'status' => 'Estado',
    ];

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $query = Objective::where('company_id', $companyId)->withAll();

        if ($request->filled('quarter_id')) {
            $query->where('quarter_id', $request->input('quarter_id'));
        } elseif ($request->filled('quarter')) {
            $query->where('quarter', $request->input('quarter'));
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }
        if ($request->filled('remi_id')) {
            $remiId = $request->input('remi_id');
            if ($remiId === 'none') {
                $query->whereNull('remi_id');
            } else {
                $query->where('remi_id', $remiId);
            }
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->input('owner_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $objectives = $query->orderBy('title')->get();

        return response()->json(['data' => ObjectiveResource::collection($objectives)]);
    }

    public function show(Request $request, $id)
    {
        $objective = Objective::where('company_id', $request->user()->company_id)
            ->withAll()
            ->findOrFail($id);

        return response()->json(['objective' => new ObjectiveResource($objective)]);
    }

    public function store(Request $request)
    {
        $this->denyStandardUserOnDepartmentMismatch($request, (int) $request->input('department_id'));

        $companyId = $request->user()->company_id;

        /**
         * Cuatrimestre final resuelto para persistir en objective.
         */
        $quarter_payload = $this->resolveQuarterPayload($request);

        $objective = Objective::create([
            'company_id' => $companyId,
            'department_id' => (int) $request->input('department_id'),
            'owner_id' => (int) $request->input('owner_id', $request->user()->id),
            'remi_id' => $request->input('remi_id') ?: null,
            'quarter_id' => $quarter_payload['quarter_id'],
            'title' => (string) $request->input('title', ''),
            'description' => $request->input('description'),
            'quarter' => $quarter_payload['quarter_name'],
            'calculation_mode' => $request->input('calculation_mode', Objective::CALC_AUTOMATIC),
            'manual_progress' => $request->input('manual_progress'),
            'manual_status' => $request->input('manual_status'),
            'progress' => 0,
            'status' => Objective::STATUS_EMPTY,
        ]);

        // trigger recalc (manual mode copies manual_* into progress/status)
        ObjectiveHelper::recalcFromKeyResults($objective);
        $objective->refresh();
        ActivityLogHelper::logCreation(
            $objective,
            'objective',
            'objetivo',
            self::LOG_FIELDS,
            $request->user(),
            [
                'company_id' => $companyId,
                'objective_id' => $objective->id,
            ]
        );

        return response()->json([
            'objective' => new ObjectiveResource($this->fullModel('Objective', $objective->id)),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $objective = Objective::where('company_id', $request->user()->company_id)->findOrFail($id);
        $before = $objective->getAttributes();

        // standard users can only edit objectives of their own department
        $this->denyStandardUserOnDepartmentMismatch($request, (int) $objective->department_id);
        // if department_id is being changed, also check new value
        if ($request->filled('department_id')) {
            $this->denyStandardUserOnDepartmentMismatch($request, (int) $request->input('department_id'));
        }

        $objective->fill($request->only([
            'title',
            'description',
            'department_id',
            'owner_id',
            'remi_id',
            'calculation_mode',
            'manual_progress',
            'manual_status',
        ]));

        if ($request->filled('quarter') || $request->filled('quarter_id')) {
            /**
             * Cuatrimestre final resuelto para actualización del objetivo.
             */
            $quarter_payload = $this->resolveQuarterPayload($request);
            $objective->quarter_id = $quarter_payload['quarter_id'];
            $objective->quarter = $quarter_payload['quarter_name'];
        }

        if ($request->has('remi_id') && ! $request->filled('remi_id')) {
            $objective->remi_id = null;
        }

        $objective->save();

        ObjectiveHelper::recalcFromKeyResults($objective);
        $objective->refresh();
        ActivityLogHelper::logUpdate(
            $objective,
            $before,
            $objective->getAttributes(),
            'objective',
            'objetivo',
            self::LOG_FIELDS,
            $request->user(),
            [
                'company_id' => $request->user()->company_id,
                'objective_id' => $objective->id,
            ]
        );

        return response()->json([
            'objective' => new ObjectiveResource($this->fullModel('Objective', $objective->id)),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $objective = Objective::where('company_id', $request->user()->company_id)->findOrFail($id);
        $this->denyStandardUserOnDepartmentMismatch($request, (int) $objective->department_id);
        ActivityLogHelper::logDeletion(
            $objective,
            'objective',
            'objetivo',
            self::LOG_FIELDS,
            $request->user(),
            [
                'company_id' => $request->user()->company_id,
                'objective_id' => $objective->id,
            ]
        );

        $objective->delete();

        return response()->json(['message' => 'Objective deleted.']);
    }

    /**
     * Standard users ('user' role) can only write objectives / KRs of their own department.
     * Admins have no restriction.
     */
    private function denyStandardUserOnDepartmentMismatch(Request $request, int $targetDeptId): void
    {
        $user = $request->user();
        if (! $user) {
            abort(response()->json(['message' => 'Unauthenticated.'], 401));
        }

        if ($user->role === User::ROLE_USER && (int) $user->department_id !== $targetDeptId) {
            abort(response()->json([
                'message' => 'Standard users can only modify objectives of their own department.',
            ], 403));
        }
    }

    /**
     * Resuelve quarter_id + quarter_name desde quarter_id o quarter textual.
     */
    private function resolveQuarterPayload(Request $request): array
    {
        /**
         * Usuario autenticado para acotar resolución de cuatrimestre a su empresa.
         */
        $user = $request->user();

        /**
         * Quarter textual recibido por request o fallback vacío.
         */
        $quarter_name = (string) $request->input('quarter', '');

        /**
         * Quarter id recibido directamente, si existe.
         */
        $quarter_id = $request->input('quarter_id');

        if ($quarter_id) {
            $quarter = Quarter::where('company_id', $user->company_id)->find($quarter_id);
            if ($quarter) {
                return [
                    'quarter_id' => $quarter->id,
                    'quarter_name' => $quarter->name,
                ];
            }
        }

        if ($quarter_name !== '') {
            $quarter = Quarter::where('company_id', $user->company_id)
                ->where('name', $quarter_name)
                ->first();

            if ($quarter) {
                return [
                    'quarter_id' => $quarter->id,
                    'quarter_name' => $quarter->name,
                ];
            }
        }

        return [
            'quarter_id' => null,
            'quarter_name' => $quarter_name,
        ];
    }
}
