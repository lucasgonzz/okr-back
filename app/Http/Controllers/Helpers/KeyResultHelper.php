<?php

namespace App\Http\Controllers\Helpers;

use App\Models\KeyResult;
use App\Models\KeyResultLog;
use App\Models\User;

class KeyResultHelper
{
    /**
     * Fields whose changes we track into key_result_logs.
     */
    public const TRACKED_FIELDS = [
        'title',
        'description',
        'target',
        'current_value',
        'start_value',
        'unit',
        'status',
        'sums_to_objective',
        'blockers',
        'comments',
        'owner_id',
    ];

    /**
     * Recompute progress for a KeyResult based on current/target values.
     * Business rule:
     *   - progress = min(round((current / target) * 100), 100)
     *
     * Status is intentionally NOT recalculated here because it is now user-controlled
     * from the create/edit form and must be persisted as provided by the API payload.
     *
     * This method mutates the model in memory but does NOT persist it.
     */
    public static function recalcProgressAndStatus(KeyResult $kr): void
    {
        $target = (float) $kr->target;
        $current = (float) $kr->current_value;

        if ($target > 0) {
            $progress = (int) min(round(($current / $target) * 100), 100);
        } else {
            $progress = 0;
        }
        $kr->progress = $progress;
    }

    /**
     * Compute the diff between two associative arrays (old vs new) limited to the tracked fields.
     * Returns a list of ['field' => ..., 'old' => ..., 'new' => ...] entries or null when no changes.
     */
    public static function diff(array $before, array $after): ?array
    {
        $changes = [];
        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $after)) {
                continue;
            }
            $oldVal = $before[$field] ?? null;
            $newVal = $after[$field] ?? null;

            // loose compare to avoid "1" vs 1 noise
            if ((string) $oldVal !== (string) $newVal) {
                $changes[] = [
                    'field' => $field,
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        return count($changes) > 0 ? $changes : null;
    }

    /**
     * Persist a log entry for a change made by $user on $kr. If there are no tracked changes
     * between $before and $after, nothing is inserted.
     */
    public static function logChanges(KeyResult $kr, array $before, array $after, ?User $user): ?KeyResultLog
    {
        $changes = self::diff($before, $after);
        if ($changes === null) {
            return null;
        }

        return KeyResultLog::create([
            'key_result_id' => $kr->id,
            'user_id' => $user ? $user->id : null,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
