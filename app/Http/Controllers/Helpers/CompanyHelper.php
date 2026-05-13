<?php

namespace App\Http\Controllers\Helpers;

use App\Models\Company;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyHelper
{
    /**
     * Create a new company along with its initial admin user in a single transaction.
     *
     * @param  array  $data  Expected keys:
     *   - name (string, required)
     *   - slug (string, optional; generated from name if missing)
     *   - active (bool, optional, defaults to true)
     *   - admin_name (string, required)
     *   - admin_email (string, required, must be unique in users)
     *   - admin_password (string, required)
     */
    public static function createWithAdmin(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
            $slug = self::uniqueSlug($slug);

            $company = Company::create([
                'name' => $data['name'],
                'slug' => $slug,
                'active' => $data['active'] ?? true,
            ]);

            self::createDefaultQuarters($company);

            $admin = User::create([
                'company_id' => $company->id,
                'department_id' => null,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role' => User::ROLE_ADMIN,
            ]);

            return ['company' => $company, 'admin' => $admin];
        });
    }

    /**
     * Primer administrador de la empresa (mismo criterio que al crear: el admin inicial tiene el id más bajo).
     */
    public static function primaryAdmin(Company $company): ?User
    {
        return User::query()
            ->where('company_id', $company->id)
            ->where('role', User::ROLE_ADMIN)
            ->orderBy('id')
            ->first();
    }

    /**
     * Crea los cuatrimestres (Q1–Q4) para el año en curso y el siguiente, para que la empresa pueda usarlos de inmediato.
     */
    private static function createDefaultQuarters(Company $company): void
    {
        $years = [now()->year, now()->year + 1];

        foreach ($years as $year) {
            for ($q = 1; $q <= 4; $q++) {
                Quarter::create([
                    'company_id' => $company->id,
                    'name' => "Q{$q}-{$year}",
                    'year' => $year,
                    'position' => $q,
                ]);
            }
        }
    }

    /**
     * Ensure the slug is unique by appending -2, -3, ... if taken.
     */
    private static function uniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'company';
        $i = 2;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
