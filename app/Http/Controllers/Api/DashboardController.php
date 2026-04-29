<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KeyResult;
use App\Models\Objective;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard/stats?quarter=Q4-2024
     * Aggregated KPIs for the current company. Mirrors DashboardStats in lib/types.ts.
     */
    public function stats(Request $request)
    {
        $companyId = $request->user()->company_id;

        $query = Objective::where('company_id', $companyId);
        if ($request->filled('quarter_id')) {
            $query->where('quarter_id', $request->input('quarter_id'));
        } elseif ($request->filled('quarter')) {
            $query->where('quarter', $request->input('quarter'));
        }
        $objectives = $query->get();

        $totalObjectives = $objectives->count();
        $completedObjectives = $objectives->where('status', Objective::STATUS_COMPLETED)->count();
        $enRiesgoObjectives = $objectives->where('status', Objective::STATUS_AT_RISK)->count();
        $sinEstadoObjectives = $objectives->where('status', Objective::STATUS_EMPTY)->count();

        $averageProgress = $totalObjectives > 0
            ? (int) round($objectives->avg('progress'))
            : 0;

        $objectiveIds = $objectives->pluck('id');
        $krQuery = KeyResult::whereIn('objective_id', $objectiveIds);
        $totalKeyResults = (clone $krQuery)->count();
        $completedKeyResults = (clone $krQuery)->where('status', KeyResult::STATUS_COMPLETED)->count();

        return response()->json([
            'totalObjectives' => $totalObjectives,
            'completedObjectives' => $completedObjectives,
            'enRiesgoObjectives' => $enRiesgoObjectives,
            'sinEstadoObjectives' => $sinEstadoObjectives,
            'averageProgress' => $averageProgress,
            'totalKeyResults' => $totalKeyResults,
            'completedKeyResults' => $completedKeyResults,
        ]);
    }
}
