<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const LOG_FIELDS = [
        'name' => 'Nombre',
        'email' => 'Correo',
        'department_id' => 'Departamento',
        'role' => 'Rol',
        'avatar' => 'Avatar',
    ];

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $users = User::where('company_id', $companyId)
            ->with('department')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => UserResource::collection($users)]);
    }

    public function show(Request $request, $id)
    {
        $user = User::where('company_id', $request->user()->company_id)
            ->with('department')
            ->findOrFail($id);

        return response()->json(['user' => new UserResource($user)]);
    }

    public function store(Request $request)
    {
        $this->denyNonAdmin($request);

        $companyId = $request->user()->company_id;
        $validated = $request->validate([
            'department_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'avatar' => ['nullable', 'string', 'max:2048'],
        ]);

        $department = Department::where('company_id', $companyId)
            ->find($validated['department_id']);
        if (! $department) {
            return response()->json(['message' => 'Selected department is not valid for your company.'], 422);
        }

        // Only admin or user roles are creatable inside a company.
        $role = $validated['role'] ?? User::ROLE_USER;
        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_USER], true)) {
            $role = User::ROLE_USER;
        }

        $user = User::create([
            'company_id' => $companyId,
            'department_id' => $validated['department_id'],
            'name' => (string) $validated['name'],
            'email' => (string) $validated['email'],
            'password' => Hash::make((string) $validated['password']),
            'role' => $role,
            'avatar' => $validated['avatar'] ?? null,
        ]);

        $user->load('department');
        ActivityLogHelper::logCreation(
            $user,
            'user',
            'usuario',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $companyId]
        );

        return response()->json(['user' => new UserResource($user)], 201);
    }

    public function update(Request $request, $id)
    {
        $this->denyNonAdmin($request);

        $user = User::where('company_id', $request->user()->company_id)->findOrFail($id);

        if (
            (int) $request->user()->id === (int) $id
            && array_key_exists('role', $request->all())
            && $request->input('role') === User::ROLE_USER
            && $request->user()->role === User::ROLE_ADMIN
        ) {
            return response()->json([
                'message' => 'No podés quitarte el rol de administrador a vos mismo.',
            ], 422);
        }

        $before = $user->getAttributes();
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'department_id' => ['sometimes', 'nullable', 'integer'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'role' => ['sometimes', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
        ]);

        if (array_key_exists('department_id', $validated) && $validated['department_id'] !== null) {
            $department = Department::where('company_id', $request->user()->company_id)
                ->find($validated['department_id']);
            if (! $department) {
                return response()->json(['message' => 'Selected department is not valid for your company.'], 422);
            }
        }

        $user->fill(collect($validated)->only(['name', 'email', 'department_id', 'avatar'])->toArray());

        if (array_key_exists('role', $validated)) {
            $newRole = $validated['role'];
            if (in_array($newRole, [User::ROLE_ADMIN, User::ROLE_USER], true)) {
                $user->role = $newRole;
            }
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make((string) $validated['password']);
        }

        $user->save();
        $user->load('department');
        $after = $user->getAttributes();
        if (! empty($validated['password'])) {
            $before['password'] = '***';
            $after['password'] = '*** actualizada ***';
        }
        ActivityLogHelper::logUpdate(
            $user,
            $before,
            $after,
            'user',
            'usuario',
            self::LOG_FIELDS + ['password' => 'Contrasena'],
            $request->user(),
            ['company_id' => $request->user()->company_id]
        );

        return response()->json(['user' => new UserResource($user)]);
    }

    public function destroy(Request $request, $id)
    {
        $this->denyNonAdmin($request);

        if ((int) $request->user()->id === (int) $id) {
            return response()->json(['message' => 'You cannot delete your own user.'], 422);
        }

        $user = User::where('company_id', $request->user()->company_id)->findOrFail($id);
        ActivityLogHelper::logDeletion(
            $user,
            'user',
            'usuario',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $request->user()->company_id]
        );
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    private function denyNonAdmin(Request $request): void
    {
        if (! $request->user() || $request->user()->role !== User::ROLE_ADMIN) {
            abort(response()->json(['message' => 'Only admins can manage users.'], 403));
        }
    }
}
