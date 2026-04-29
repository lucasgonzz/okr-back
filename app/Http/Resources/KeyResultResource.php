<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KeyResultResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'objectiveId' => (string) $this->objective_id,
            'title' => $this->title,
            'description' => $this->description,
            'owner' => $this->whenLoaded('owner', function () {
                return new UserResource($this->owner);
            }),
            'status' => $this->status,
            'progress' => (int) $this->progress,
            'target' => (float) $this->target,
            'currentValue' => (float) $this->current_value,
            'startValue' => (float) $this->start_value,
            'unit' => $this->unit,
            'sumsToObjective' => (bool) $this->sums_to_objective,
            'blockers' => $this->blockers ?? '',
            'comments' => $this->comments ?? '',
        ];
    }
}
