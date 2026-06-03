<?php

namespace App\Http\Controllers\Helpers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAssignmentHelper
{
    /**
     * Rechaza la petición si el usuario no pertenece a la empresa o está eliminado (soft delete).
     */
    public static function rejectIfNotAssignable(Request $request, ?int $userId, string $field = 'owner_id'): ?JsonResponse
    {
        if ($userId === null || $userId <= 0) {
            return null;
        }

        $companyId = $request->user()->company_id;
        $assignable = User::where('company_id', $companyId)
            ->where('id', $userId)
            ->exists();

        if (! $assignable) {
            return response()->json([
                'message' => 'El usuario seleccionado no es válido o no está disponible para asignar.',
                'errors' => [
                    $field => ['El usuario seleccionado no es válido o no está disponible para asignar.'],
                ],
            ], 422);
        }

        return null;
    }
}
