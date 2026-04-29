<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Reload a model applying its withAll() scope, so every JSON response keeps
     * the same relationship shape regardless of whether it comes from index/show/store/update.
     *
     * @param  string  $modelName  Short class name in App\Models (e.g. "Objective", "KeyResult")
     * @param  int|string  $id
     */
    protected function fullModel(string $modelName, $id)
    {
        $class = '\\App\\Models\\' . $modelName;
        return $class::withAll()->findOrFail($id);
    }
}
