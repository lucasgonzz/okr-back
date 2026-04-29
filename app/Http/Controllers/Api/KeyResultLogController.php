<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KeyResultLogResource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class KeyResultLogController extends Controller
{
    /**
     * GET /api/v1/key-result-logs?objective_id=&key_result_id=&user_id=&date_from=&date_to=
     * Returns activity history scoped to the current company.
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $query = ActivityLog::query()
            ->where('company_id', $companyId)
            ->with('user');

        if ($request->filled('objective_id')) {
            $query->where('objective_id', $request->input('objective_id'));
        }

        if ($request->filled('key_result_id')) {
            $query->where('key_result_id', $request->input('key_result_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderByDesc('created_at')->limit(500)->get();

        return response()->json(['data' => KeyResultLogResource::collection($logs)]);
    }

    /**
     * GET /api/v1/key-results/{id}/logs
     */
    public function forKeyResult(Request $request, $keyResultId)
    {
        $logs = ActivityLog::query()
            ->where('company_id', $request->user()->company_id)
            ->where('key_result_id', $keyResultId)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => KeyResultLogResource::collection($logs)]);
    }
}
