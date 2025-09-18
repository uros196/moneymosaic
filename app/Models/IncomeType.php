<?php

namespace App\Models;

use App\Policies\IncomeTypePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[UsePolicy(IncomeTypePolicy::class)]
class IncomeType extends Model
{
    use HasFactory, HasTranslations;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * The attributes that are translatable.
     */
    public array $translatable = ['name'];

    /**
     * Owner user (null means a system type).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Incomes using this type.
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }
}
