<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Controllers\Helpers\KeyResultHelper;
use App\Http\Controllers\Helpers\ObjectiveHelper;
use App\Http\Resources\KeyResultResource;
use App\Models\KeyResult;
use App\Models\Objective;
use App\Models\User;
use Illuminate\Http\Request;

class KeyResultController extends Controller
{
    private const LOG_FIELDS = [
        'title' => 'Titulo',
        'description' => 'Descripcion',
        'target' => 'Valor meta',
        'current_value' => 'Valor actual',
        'start_value' => 'Valor inicial',
        'unit' => 'Unidad',
        'status' => 'Estado',
        'progress' => 'Progreso',
        'sums_to_objective' => 'Suma al objetivo',
        'blockers' => 'Bloqueadores',
        'comments' => 'Comentarios',
        'owner_id' => 'Responsable',
    ];

    /**
     * GET /api/v1/objectives/{objective}/key-results
     */
    public function index(Request $request, $objectiveId)
    {
        $objective = $this->findObjectiveInCompany($request, $objectiveId);

        $krs = $objective->keyResults()->with('owner')->orderBy('id')->get();

        return response()->json(['data' => KeyResultResource::collection($krs)]);
    }

    /**
     * POST /api/v1/objectives/{objective}/key-results
     */
    public function store(Request $request, $objectiveId)
    {
        $objective = $this->findObjectiveInCompany($request, $objectiveId);
        $this->denyStandardUserOnDepartmentMismatch($request, (int) $objective->department_id);

        $kr = new KeyResult([
            'objective_id' => $objective->id,
            'owner_id' => (int) $request->input('owner_id', $request->user()->id),
            'title' => (string) $request->input('title', ''),
            'description' => $request->input('description'),
            'target' => (float) $request->input('target', 0),
            'current_value' => (float) $request->input('current_value', $request->input('currentValue', 0)),
            'start_value' => (float) $request->input('start_value', $request->input('startValue', 0)),
            'unit' => (string) $request->input('unit', ''),
            'status' => $this->normalizeStatusInput($request->input('status', KeyResult::STATUS_EMPTY)),
            'sums_to_objective' => (bool) $request->input('sums_to_objective', $request->input('sumsToObjective', true)),
            'blockers' => $request->input('blockers'),
            'comments' => $request->input('comments'),
        ]);

        KeyResultHelper::recalcProgressAndStatus($kr);
        $kr->save();

        ActivityLogHelper::logCreation(
            $kr,
            'key_result',
            'key result',
            self::LOG_FIELDS,
            $request->user(),
            [
                'company_id' => $request->user()->company_id,
                'objective_id' => $objective->id,
                'key_result_id' => $kr->id,
            ]
        );

        // Recalc parent objective
        ObjectiveHelper::recalcFromKeyResults($objective->fresh());

        return response()->json([
            'keyResult' => new KeyResultResource($this->fullModel('KeyResult', $kr->id)),
        ], 201);
    }

    /**
     * PUT /api/v1/objectives/{objective}/key-results/{id}
     */
    public function update(Request $request, $objectiveId, $id)
    {
        $objective = $this->findObjectiveInCompany($request, $objectiveId);
        $this->denyStandardUserOnDepartmentMismatch($request, (int) $objective->department_id);

        $kr = KeyResult::where('objective_id', $objective->id)->findOrFail($id);

        // snapshot BEFORE mutations
        $before = $kr->getAttributes();

        // Accept both snake_case and camelCase inputs from the front
        $map = [
            'title' => 'title',
            'description' => 'description',
            'target' => 'target',
            'current_value' => 'current_value',
            'currentValue' => 'current_value',
            'start_value' => 'start_value',
            'startValue' => 'start_value',
            'unit' => 'unit',
            'status' => 'status',
            'sums_to_objective' => 'sums_to_objective',
            'sumsToObjective' => 'sums_to_objective',
            'blockers' => 'blockers',
            'comments' => 'comments',
            'owner_id' => 'owner_id',
            'ownerId' => 'owner_id',
        ];

        foreach ($map as $input => $column) {
            if ($request->has($input)) {
                $kr->{$column} = $request->input($input);
            }
        }
        if ($request->has('status')) {
            $kr->status = $this->normalizeStatusInput($request->input('status'));
        }

        KeyResultHelper::recalcProgressAndStatus($kr);
        $kr->save();

        ActivityLogHelper::logUpdate(
            $kr,
            $before,
            $kr->getAttributes(),
            'key_result',
            'key result',
            self::LOG_FIELDS,
            $request->user(),
            [
                'company_id' => $request->user()->company_id,
                'objective_id' => $objective->id,
                'key_result_id' => $kr->id,
            ]
        );

        // Recalc parent objective
        ObjectiveHelper::recalcFromKeyResults($objective->fresh());

        return response()->json([
            'keyResult' => new KeyResultResource($this->fullModel('KeyResult', $kr->id)),
        ]);
    }

    /**
     * DELETE /api/v1/objectives/{objective}/key-results/{id}
     */
    public function destroy(Request $request, $objectiveId, $id)
    {
        $objective = $this->findObjectiveInCompany($request, $objectiveId);
        $this->denyStandardUserOnDepartmentMismatch($request, (int) $objective->department_id);

        $kr = KeyResult::where('objective_id', $objective->id)->findOrFail($id);
        ActivityLogHelper::logDeletion(
            $kr,
            'key_result',
            'key result',
            self::LOG_FIELDS,
            $request->user(),
            [
                'company_id' => $request->user()->company_id,
                'objective_id' => $objective->id,
                'key_result_id' => $kr->id,
            ]
        );
        $kr->delete();

        ObjectiveHelper::recalcFromKeyResults($objective->fresh());

        return response()->json(['message' => 'Key result deleted.']);
    }

    private function findObjectiveInCompany(Request $request, $objectiveId): Objective
    {
        return Objective::where('company_id', $request->user()->company_id)->findOrFail($objectiveId);
    }

    private function denyStandardUserOnDepartmentMismatch(Request $request, int $targetDeptId): void
    {
        $user = $request->user();
        if ($user->role === User::ROLE_USER && (int) $user->department_id !== $targetDeptId) {
            abort(response()->json([
                'message' => 'Standard users can only modify key results of their own department.',
            ], 403));
        }
    }

    /**
     * Normaliza estados vacios para evitar persistir NULL en una columna NOT NULL.
     * "sin asignar"/"unassigned" se guardan como estado vacio.
     */
    private function normalizeStatusInput($status): string
    {
        if ($status === null) {
            return KeyResult::STATUS_EMPTY;
        }

        $status = trim((string) $status);
        if ($status === '') {
            return KeyResult::STATUS_EMPTY;
        }

        $normalized = mb_strtolower($status, 'UTF-8');
        if (in_array($normalized, ['sin asignar', 'unassigned'], true)) {
            return KeyResult::STATUS_EMPTY;
        }

        return $status;
    }
}
