<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RemiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'companyId' => (string) $this->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'responsibleUser' => $this->whenLoaded('responsibleUser', function () {
                return $this->responsibleUser ? new UserResource($this->responsibleUser) : null;
            }),
            'responsibleDepartment' => $this->whenLoaded('responsibleDepartment', function () {
                return $this->responsibleDepartment ? new DepartmentResource($this->responsibleDepartment) : null;
            }),
            'progresoManual' => (int) ($this->progreso_manual ?? 0),
        ];
    }
}
