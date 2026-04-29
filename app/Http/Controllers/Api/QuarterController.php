<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuarterResource;
use App\Models\Quarter;
use Illuminate\Http\Request;

class QuarterController extends Controller
{
    /**
     * Lista de cuatrimestres disponibles para la empresa actual.
     */
    public function index(Request $request)
    {
        $quarters = Quarter::where('company_id', $request->user()->company_id)
            ->orderBy('year')
            ->orderBy('position')
            ->get();

        return response()->json([
            'data' => QuarterResource::collection($quarters),
        ]);
    }
}
