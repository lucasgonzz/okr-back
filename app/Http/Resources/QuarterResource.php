<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuarterResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'companyId' => (string) $this->company_id,
            'name' => $this->name,
            'year' => (int) $this->year,
            'position' => (int) $this->position,
        ];
    }
}
