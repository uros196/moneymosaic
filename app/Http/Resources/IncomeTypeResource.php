<?php

namespace App\Http\Resources;

use App\Models\IncomeType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IncomeType
 */
class IncomeTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'incomes_count' => $this->whenNotNull($this->incomes_count),
            'is_system' => $this->is_system_type,
        ];
    }
}
