<?php

namespace App\Http\Controllers\Helpers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogHelper
{
    public const OP_CREATE = 'create';
    public const OP_UPDATE = 'update';
    public const OP_DELETE = 'delete';
    private const BOOLEAN_FIELDS = [
        'sums_to_objective',
        'active',
    ];

    public static function logCreation(
        Model $model,
        string $resourceType,
        string $resourceNameEs,
        array $fieldLabels,
        ?User $actor,
        array $context = []
    ): ?ActivityLog {
        $after = $model->getAttributes();
        $changes = self::buildCreateChanges($after, $fieldLabels);

        if ($changes === []) {
            return null;
        }

        return self::persist(self::OP_CREATE, $resourceType, $resourceNameEs, $model->getKey(), $changes, $actor, $context);
    }

    public static function logUpdate(
        Model $model,
        array $before,
        array $after,
        string $resourceType,
        string $resourceNameEs,
        array $fieldLabels,
        ?User $actor,
        array $context = []
    ): ?ActivityLog {
        $changes = self::buildDiffChanges($before, $after, $fieldLabels);

        if ($changes === []) {
            return null;
        }

        return self::persist(self::OP_UPDATE, $resourceType, $resourceNameEs, $model->getKey(), $changes, $actor, $context);
    }

    public static function logDeletion(
        Model $model,
        string $resourceType,
        string $resourceNameEs,
        array $fieldLabels,
        ?User $actor,
        array $context = []
    ): ?ActivityLog {
        $before = $model->getAttributes();
        $changes = self::buildDeleteChanges($before, $fieldLabels);

        return self::persist(self::OP_DELETE, $resourceType, $resourceNameEs, $model->getKey(), $changes, $actor, $context);
    }

    private static function persist(
        string $operation,
        string $resourceType,
        string $resourceNameEs,
        $resourceId,
        array $changes,
        ?User $actor,
        array $context
    ): ActivityLog {
        $titleByOperation = [
            self::OP_CREATE => 'Creacion de ',
            self::OP_UPDATE => 'Actualizacion de ',
            self::OP_DELETE => 'Eliminacion de ',
        ];

        return ActivityLog::create([
            'company_id' => $context['company_id'] ?? ($actor ? $actor->company_id : null),
            'user_id' => $actor ? $actor->id : null,
            'operation' => $operation,
            'title' => $titleByOperation[$operation] . $resourceNameEs,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId ? (int) $resourceId : null,
            'objective_id' => isset($context['objective_id']) ? (int) $context['objective_id'] : null,
            'key_result_id' => isset($context['key_result_id']) ? (int) $context['key_result_id'] : null,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }

    private static function buildCreateChanges(array $after, array $fieldLabels): array
    {
        $changes = [];
        foreach ($fieldLabels as $field => $label) {
            if (! array_key_exists($field, $after)) {
                continue;
            }
            $newVal = self::normalizeForField($field, $after[$field]);
            if ($newVal === null) {
                continue;
            }
            $changes[] = [
                'field' => $field,
                'property' => $label,
                'old' => null,
                'new' => $newVal,
            ];
        }

        return $changes;
    }

    private static function buildDiffChanges(array $before, array $after, array $fieldLabels): array
    {
        $changes = [];
        foreach ($fieldLabels as $field => $label) {
            if (! array_key_exists($field, $after)) {
                continue;
            }

            $oldVal = $before[$field] ?? null;
            $newVal = $after[$field] ?? null;

            if (! self::valuesAreDifferent($field, $oldVal, $newVal)) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'property' => $label,
                'old' => self::normalizeForField($field, $oldVal),
                'new' => self::normalizeForField($field, $newVal),
            ];
        }

        return $changes;
    }

    private static function buildDeleteChanges(array $before, array $fieldLabels): array
    {
        $changes = [];
        foreach ($fieldLabels as $field => $label) {
            if (! array_key_exists($field, $before)) {
                continue;
            }
            $oldVal = self::normalizeForField($field, $before[$field]);
            if ($oldVal === null) {
                continue;
            }
            $changes[] = [
                'field' => $field,
                'property' => $label,
                'old' => $oldVal,
                'new' => null,
            ];
        }

        return $changes === [] ? [[
            'field' => 'registro',
            'property' => 'Registro',
            'old' => 'El recurso fue eliminado',
            'new' => null,
        ]] : $changes;
    }

    private static function normalizeValue($value)
    {
        if (is_bool($value)) {
            return $value ? 'Si' : 'No';
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }

        return $value;
    }

    private static function valuesAreDifferent(string $field, $oldValue, $newValue): bool
    {
        $old = self::normalizeComparable($field, $oldValue);
        $new = self::normalizeComparable($field, $newValue);

        return $old !== $new;
    }

    private static function normalizeComparable(string $field, $value)
    {
        $normalized = self::normalizeForField($field, $value);
        if ($normalized === null) {
            return null;
        }

        if (is_numeric($normalized)) {
            return (float) $normalized;
        }

        return (string) $normalized;
    }

    private static function normalizeForField(string $field, $value)
    {
        if (in_array($field, self::BOOLEAN_FIELDS, true)) {
            if ($value === null || $value === '') {
                return null;
            }

            if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
                return 'Si';
            }

            if ($value === false || $value === 0 || $value === '0' || $value === 'false') {
                return 'No';
            }
        }

        return self::normalizeValue($value);
    }
}
