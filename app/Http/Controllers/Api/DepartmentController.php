<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    private const LOG_FIELDS = [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'color' => 'Color',
    ];

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $departments = Department::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => DepartmentResource::collection($departments)]);
    }

    public function show(Request $request, $id)
    {
        $department = Department::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        return response()->json(['department' => new DepartmentResource($department)]);
    }

    public function store(Request $request)
    {
        $this->denyNonAdmin($request);

        $companyId = $request->user()->company_id;
        $slug = $request->input('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name', ''));

        $department = Department::create([
            'company_id' => $companyId,
            'name' => (string) $request->input('name', ''),
            'slug' => $slug !== '' ? $slug : 'dept-' . time(),
            'color' => (string) $request->input('color', '#6366F1'),
        ]);

        ActivityLogHelper::logCreation(
            $department,
            'department',
            'departamento',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $companyId]
        );

        return response()->json(['department' => new DepartmentResource($department)], 201);
    }

    public function update(Request $request, $id)
    {
        $this->denyNonAdmin($request);

        $department = Department::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $before = $department->getAttributes();
        $department->fill($request->only(['name', 'slug', 'color']));
        if ($request->filled('slug')) {
            $department->slug = Str::slug($request->input('slug'));
        }
        $department->save();
        ActivityLogHelper::logUpdate(
            $department,
            $before,
            $department->getAttributes(),
            'department',
            'departamento',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $request->user()->company_id]
        );

        return response()->json(['department' => new DepartmentResource($department)]);
    }

    public function destroy(Request $request, $id)
    {
        $this->denyNonAdmin($request);

        $department = Department::where('company_id', $request->user()->company_id)
            ->findOrFail($id);
        ActivityLogHelper::logDeletion(
            $department,
            'department',
            'departamento',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $request->user()->company_id]
        );
        $department->delete();

        return response()->json(['message' => 'Department deleted.']);
    }

    private function denyNonAdmin(Request $request): void
    {
        if (! $request->user() || $request->user()->role !== User::ROLE_ADMIN) {
            abort(response()->json(['message' => 'Only admins can modify departments.'], 403));
        }
    }
}
