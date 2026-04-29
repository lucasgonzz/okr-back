<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ObjectiveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'quarter' => $this->quarterModel ? $this->quarterModel->name : $this->quarter,
            'quarterId' => $this->quarter_id ? (string) $this->quarter_id : null,
            'quarterModel' => $this->whenLoaded('quarterModel', function () {
                return $this->quarterModel ? new QuarterResource($this->quarterModel) : null;
            }),
            'department' => $this->whenLoaded('department', function () {
                return new DepartmentResource($this->department);
            }),
            'owner' => $this->whenLoaded('owner', function () {
                return new UserResource($this->owner);
            }),
            'remi' => $this->whenLoaded('remi', function () {
                return $this->remi ? new RemiResource($this->remi) : null;
            }),
            'status' => $this->status,
            'progress' => (int) $this->progress,
            'calculationMode' => $this->calculation_mode,
            'manualProgress' => $this->manual_progress !== null ? (int) $this->manual_progress : null,
            'manualStatus' => $this->manual_status,
            'keyResults' => KeyResultResource::collection($this->whenLoaded('keyResults')),
            'createdAt' => optional($this->created_at)->toISOString(),
            'updatedAt' => optional($this->updated_at)->toISOString(),
        ];
    }
}
