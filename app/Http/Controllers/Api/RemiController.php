<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Controllers\Helpers\UserAssignmentHelper;
use App\Http\Resources\RemiResource;
use App\Models\Quarter;
use App\Models\Remi;
use App\Models\User;
use Illuminate\Http\Request;

class RemiController extends Controller
{
    private const LOG_FIELDS = [
        'name' => 'Nombre',
        'description' => 'Descripcion',
        'responsible_user_id' => 'Responsable (usuario)',
        'responsible_department_id' => 'Responsable (departamento)',
        'progreso_manual' => 'Progreso manual (%)',
    ];

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $remis = Remi::where('company_id', $companyId)
            ->withAll()
            ->orderBy('name')
            ->get();

        return response()->json(['data' => RemiResource::collection($remis)]);
    }

    public function show(Request $request, $id)
    {
        $remi = Remi::where('company_id', $request->user()->company_id)
            ->withAll()
            ->findOrFail($id);

        return response()->json(['remi' => new RemiResource($remi)]);
    }

    public function store(Request $request)
    {
        $this->denyNonAdmin($request);

        $companyId = $request->user()->company_id;

        if ($request->filled('responsible_user_id')) {
            $responsibleUserId = (int) $request->input('responsible_user_id');
            if ($response = UserAssignmentHelper::rejectIfNotAssignable(
                $request,
                $responsibleUserId,
                'responsible_user_id'
            )) {
                return $response;
            }
        }

        $remi = Remi::create([
            'company_id' => $companyId,
            'name' => (string) $request->input('name', ''),
            'description' => $request->input('description'),
            'responsible_user_id' => $request->input('responsible_user_id'),
            'responsible_department_id' => $request->input('responsible_department_id'),
            'progreso_manual' => self::clampProgresoManual($request->input('progreso_manual')),
        ]);
        $this->syncQuarters($remi, $request, $companyId);

        ActivityLogHelper::logCreation(
            $remi,
            'remi',
            'remi',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $companyId]
        );

        return response()->json([
            'remi' => new RemiResource($this->fullModel('Remi', $remi->id)),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $this->denyNonAdmin($request);

        $remi = Remi::where('company_id', $request->user()->company_id)->findOrFail($id);
        $before = $remi->getAttributes();

        if ($request->filled('responsible_user_id')) {
            $responsibleUserId = (int) $request->input('responsible_user_id');
            if ($response = UserAssignmentHelper::rejectIfNotAssignable(
                $request,
                $responsibleUserId,
                'responsible_user_id'
            )) {
                return $response;
            }
        }

        $remi->fill($request->only([
            'name',
            'description',
            'responsible_user_id',
            'responsible_department_id',
        ]));
        if ($request->has('progreso_manual')) {
            $remi->progreso_manual = self::clampProgresoManual($request->input('progreso_manual'));
        }
        $remi->save();
        $this->syncQuarters($remi, $request, $request->user()->company_id);
        ActivityLogHelper::logUpdate(
            $remi,
            $before,
            $remi->getAttributes(),
            'remi',
            'remi',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $request->user()->company_id]
        );

        return response()->json([
            'remi' => new RemiResource($this->fullModel('Remi', $remi->id)),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->denyNonAdmin($request);

        $remi = Remi::where('company_id', $request->user()->company_id)->findOrFail($id);
        ActivityLogHelper::logDeletion(
            $remi,
            'remi',
            'remi',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $request->user()->company_id]
        );
        $remi->delete();

        return response()->json(['message' => 'Remi deleted.']);
    }

    private function denyNonAdmin(Request $request): void
    {
        if (! $request->user() || $request->user()->role !== User::ROLE_ADMIN) {
            abort(response()->json(['message' => 'Only admins can manage REMIs.'], 403));
        }
    }

    private function syncQuarters(Remi $remi, Request $request, int $companyId): void
    {
        if (! $request->has('quarter_ids')) {
            return;
        }

        $ids = collect($request->input('quarter_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $validIds = Quarter::where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $remi->quarters()->sync($validIds);
    }

    private static function clampProgresoManual($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $n = (int) $value;

        return max(0, min(100, $n));
    }
}
