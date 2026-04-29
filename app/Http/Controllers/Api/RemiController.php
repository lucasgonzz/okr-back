<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Resources\RemiResource;
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

        $remi = Remi::create([
            'company_id' => $companyId,
            'name' => (string) $request->input('name', ''),
            'description' => $request->input('description'),
            'responsible_user_id' => $request->input('responsible_user_id'),
            'responsible_department_id' => $request->input('responsible_department_id'),
        ]);
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
        $remi->fill($request->only([
            'name',
            'description',
            'responsible_user_id',
            'responsible_department_id',
        ]));
        $remi->save();
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
}
