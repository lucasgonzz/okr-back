<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'role' => $this->role,
            'companyId' => $this->company_id ? (string) $this->company_id : null,
            'departmentId' => $this->department_id ? (string) $this->department_id : null,
            // Frontend type (types.ts) expects `department: string` (human name). Keep name fallback.
            'department' => $this->whenLoaded('department', function () {
                return $this->department ? $this->department->name : null;
            }, $this->department_id ? null : ''),
        ];
    }
}
