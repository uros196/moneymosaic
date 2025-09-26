<?php

namespace App\Http\Resources;

use App\Enums\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $label = $this->label();

        return [
            'value' => $this->value,
            'label' => $label,
            'display_name' => "{$label} ({$this->value})",
            'step' => $this->step(),
        ];
    }
}
