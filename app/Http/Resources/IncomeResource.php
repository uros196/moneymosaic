<?php

namespace App\Http\Resources;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'income_type' => IncomeTypeResource::make($this->whenLoaded('incomeType')),
            'description' => $this->description,
            'description_short' => Str::limit($this->description),
            'occurred_on' => $this->occurred_on->format('Y-m-d'),
            'occurred_on_display' => $this->occurred_on->translatedFormat('d F Y'),
            'tags' => TagListResource::collection($this->whenLoaded('tags')),
        ];
    }
}
