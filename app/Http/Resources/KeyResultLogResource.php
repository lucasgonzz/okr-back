<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KeyResultLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'operation' => $this->operation,
            'resourceType' => $this->resource_type,
            'resourceId' => $this->resource_id ? (string) $this->resource_id : null,
            'objectiveId' => $this->objective_id ? (string) $this->objective_id : null,
            'keyResultId' => $this->key_result_id ? (string) $this->key_result_id : null,
            'userId' => $this->user_id ? (string) $this->user_id : null,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? new UserResource($this->user) : null;
            }),
            'changes' => $this->changes,
            'createdAt' => optional($this->created_at)->toISOString(),
        ];
    }
}
