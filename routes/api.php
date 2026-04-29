<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\KeyResultController;
use App\Http\Controllers\Api\KeyResultLogController;
use App\Http\Controllers\Api\ObjectiveController;
use App\Http\Controllers\Api\QuarterController;
use App\Http\Controllers\Api\RemiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated routes (Sanctum bearer token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Super admin only: manage platform customers (companies)
        Route::middleware('role:super_admin')->prefix('admin')->group(function () {
            Route::get('/companies', [CompanyController::class, 'index']);
            Route::post('/companies', [CompanyController::class, 'store']);
            Route::get('/companies/{id}', [CompanyController::class, 'show']);
            Route::put('/companies/{id}', [CompanyController::class, 'update']);
            Route::patch('/companies/{id}', [CompanyController::class, 'update']);
            Route::delete('/companies/{id}', [CompanyController::class, 'destroy']);
        });

        // Company-scoped: admin + user (role-level checks for writes inside controllers)
        Route::middleware(['role:admin,user', 'company.scope'])->group(function () {
            // Departments
            Route::get('/departments', [DepartmentController::class, 'index']);
            Route::post('/departments', [DepartmentController::class, 'store']);
            Route::get('/departments/{id}', [DepartmentController::class, 'show']);
            Route::put('/departments/{id}', [DepartmentController::class, 'update']);
            Route::patch('/departments/{id}', [DepartmentController::class, 'update']);
            Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

            // Users
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::patch('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);

            // REMIs
            Route::get('/remis', [RemiController::class, 'index']);
            Route::post('/remis', [RemiController::class, 'store']);
            Route::get('/remis/{id}', [RemiController::class, 'show']);
            Route::put('/remis/{id}', [RemiController::class, 'update']);
            Route::patch('/remis/{id}', [RemiController::class, 'update']);
            Route::delete('/remis/{id}', [RemiController::class, 'destroy']);

            // Quarters (cuatrimestres)
            Route::get('/quarters', [QuarterController::class, 'index']);

            // Objectives
            Route::get('/objectives', [ObjectiveController::class, 'index']);
            Route::post('/objectives', [ObjectiveController::class, 'store']);
            Route::get('/objectives/{id}', [ObjectiveController::class, 'show']);
            Route::put('/objectives/{id}', [ObjectiveController::class, 'update']);
            Route::patch('/objectives/{id}', [ObjectiveController::class, 'update']);
            Route::delete('/objectives/{id}', [ObjectiveController::class, 'destroy']);

            // Key Results (nested under objective)
            Route::get('/objectives/{objective}/key-results', [KeyResultController::class, 'index']);
            Route::post('/objectives/{objective}/key-results', [KeyResultController::class, 'store']);
            Route::put('/objectives/{objective}/key-results/{id}', [KeyResultController::class, 'update']);
            Route::patch('/objectives/{objective}/key-results/{id}', [KeyResultController::class, 'update']);
            Route::delete('/objectives/{objective}/key-results/{id}', [KeyResultController::class, 'destroy']);

            // Logs
            Route::get('/key-result-logs', [KeyResultLogController::class, 'index']);
            Route::get('/key-results/{id}/logs', [KeyResultLogController::class, 'forKeyResult']);

            // Dashboard
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        });
    });
});
