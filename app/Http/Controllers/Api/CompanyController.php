<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ActivityLogHelper;
use App\Http\Controllers\Helpers\CompanyHelper;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Super admin only: manage platform customers (companies) and their initial admin.
 */
class CompanyController extends Controller
{
    private const LOG_FIELDS = [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'active' => 'Activa',
    ];

    public function index()
    {
        $companies = Company::orderBy('name')->withCount(['users', 'departments', 'objectives'])->get();
        return response()->json(['data' => CompanyResource::collection($companies)]);
    }

    public function show($id)
    {
        $company = Company::with(['admins'])->findOrFail($id);

        return response()->json([
            'company' => new CompanyResource($company),
            'admins' => UserResource::collection($company->admins),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'name',
            'slug',
            'active',
            'admin_name',
            'admin_email',
            'admin_password',
        ]);

        $result = CompanyHelper::createWithAdmin($data);
        ActivityLogHelper::logCreation(
            $result['company'],
            'company',
            'empresa',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $result['company']->id]
        );

        return response()->json([
            'company' => new CompanyResource($result['company']),
            'admin' => new UserResource($result['admin']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $before = $company->getAttributes();
        $company->fill($request->only(['name', 'slug', 'active']));
        $company->save();
        ActivityLogHelper::logUpdate(
            $company,
            $before,
            $company->getAttributes(),
            'company',
            'empresa',
            self::LOG_FIELDS,
            $request->user(),
            ['company_id' => $company->id]
        );

        return response()->json(['company' => new CompanyResource($company)]);
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        ActivityLogHelper::logDeletion(
            $company,
            'company',
            'empresa',
            self::LOG_FIELDS,
            request()->user(),
            ['company_id' => $company->id]
        );
        $company->delete();

        return response()->json(['message' => 'Company deleted.']);
    }
}
