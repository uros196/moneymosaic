<?php

namespace App\Http\Resources;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Income
 */
class IncomeResource extends JsonResource
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
            'amount' => $this->amount,
            'currency_code' => $this->currency_code->value,
            'income_type_id' => $this->income_type_id,
            'description' => $this->description,
            'occurred_on' => $this->occurred_on->format('Y-m-d'),
            'tags' => TagListResource::collection($this->whenLoaded('tags')),
        ];
    }
}
