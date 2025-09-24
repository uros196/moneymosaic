<?php

namespace App\Http\Resources;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * IncomeResource transforms an Income model into a JSON-friendly array for the frontend.
 *
 * Notes:
 * - Dates: "occurred_on" => Y-m-d, and "occurred_on_display" => localized 'd F Y'.
 * - Currency: "currency_code" is emitted as the enum string value.
 * - Tags: exposes both a collection of tag resources ("tags") and a simple list of names ("tags_list").
 *
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
            'amount_formatted' => $this->formatted_amount,
            'currency_code' => $this->currency_code->value,
            'income_type_id' => $this->income_type_id,
            'income_type' => IncomeTypeResource::make($this->whenLoaded('incomeType')),
            'description' => $this->description,
            'description_short' => Str::limit($this->description),
            'occurred_on' => $this->occurred_on->format('Y-m-d'),
            'occurred_on_display' => $this->occurred_on->translatedFormat('F d, Y'),
            'tags' => TagListResource::collection($this->whenLoaded('tags')),
            'tags_list' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
        ];
    }
}
