<?php

namespace App\Http\Controllers\Helpers;

use App\Models\KeyResult;
use App\Models\Objective;

class ObjectiveHelper
{
    /**
     * Recompute progress and status of an Objective following the spec:
     *   - manual mode: copy manual_progress and manual_status verbatim
     *   - automatic mode:
     *       * relevant KRs = those with sums_to_objective = true
     *       * progress    = round(average(progress of relevant KRs))
     *       * status      = "completado" if ALL relevant KRs are completed
     *                       "en-riesgo"  if at least one relevant KR is at risk
     *                       ""           otherwise
     *       * if there are no relevant KRs, progress = 0 and status = ""
     *
     * Persists the result.
     */
    public static function recalcFromKeyResults(Objective $objective): Objective
    {
        if ($objective->calculation_mode === Objective::CALC_MANUAL) {
            $objective->progress = (int) ($objective->manual_progress ?? 0);
            $objective->status = (string) ($objective->manual_status ?? '');
            $objective->save();
            return $objective;
        }

        $relevant = $objective->keyResults()
            ->where('sums_to_objective', true)
            ->get();

        if ($relevant->isEmpty()) {
            $objective->progress = 0;
            $objective->status = Objective::STATUS_EMPTY;
            $objective->save();
            return $objective;
        }

        $progress = (int) round($relevant->avg('progress'));

        $allCompleted = $relevant->every(function (KeyResult $kr) {
            return $kr->status === KeyResult::STATUS_COMPLETED;
        });
        $anyAtRisk = $relevant->contains(function (KeyResult $kr) {
            return $kr->status === KeyResult::STATUS_AT_RISK;
        });

        if ($allCompleted) {
            $status = Objective::STATUS_COMPLETED;
        } elseif ($anyAtRisk) {
            $status = Objective::STATUS_AT_RISK;
        } else {
            $status = Objective::STATUS_EMPTY;
        }

        $objective->progress = $progress;
        $objective->status = $status;
        $objective->save();

        return $objective;
    }
}
