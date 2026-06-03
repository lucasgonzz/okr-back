<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait AuthorizesDepartmentScope
{
    /**
     * Usuarios con rol "user" solo pueden escribir recursos de su departamento.
     * Los admin no tienen restricción.
     */
    protected function denyStandardUserOnDepartmentMismatch(Request $request, ?int $targetDeptId): void
    {
        $user = $request->user();
        if (! $user || ! $user->isStandardUser()) {
            return;
        }

        if (! $targetDeptId) {
            abort(response()->json([
                'message' => 'Este recurso no tiene departamento asignado. Solo un administrador puede modificarlo.',
            ], 403));
        }

        if (! $user->department_id) {
            abort(response()->json([
                'message' => 'Tu usuario no tiene un departamento asignado.',
            ], 403));
        }

        if ((int) $user->department_id !== $targetDeptId) {
            abort(response()->json([
                'message' => 'Solo puedes modificar objetivos y key results de tu departamento.',
            ], 403));
        }
    }

    /**
     * Fuerza el department_id del usuario estándar en creación/actualización.
     */
    protected function resolveDepartmentIdForWrite(Request $request): int
    {
        $user = $request->user();

        if ($user && $user->isStandardUser()) {
            if (! $user->department_id) {
                abort(response()->json([
                    'message' => 'Tu usuario no tiene un departamento asignado.',
                ], 403));
            }

            return (int) $user->department_id;
        }

        return (int) $request->input('department_id');
    }
}
