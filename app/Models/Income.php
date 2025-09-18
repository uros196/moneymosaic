<?php

namespace App\Models;

use App\Casts\EncryptedInteger;
use App\Enums\Currency;
use App\Policies\IncomePolicy;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

#[UsePolicy(IncomePolicy::class)]
class Income extends Model
{
    use HasFactory, HasTags;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'amount_minor',
        'currency_code',
        'income_type_id',
        'description',
        'occurred_on',
    ];

    /**
     * Attribute casts for the model.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            // Encrypted attributes
            'name' => 'encrypted',
            'description' => 'encrypted',
            'amount_minor' => EncryptedInteger::class,

            // Other casts
            'occurred_on' => 'date',
            'currency_code' => Currency::class,
        ];
    }

    /**
     * The user that owns the income.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Linked income type.
     */
    public function incomeType(): BelongsTo
    {
        return $this->belongsTo(IncomeType::class);
    }

    /**
     * This accessor converts the stored minor amount and currency code
     * into a value for convenient handling of monetary values.
     */
    public function amount(): Attribute
    {
        return Attribute::make(
            get: fn () => Money::fromMinor($this->amount_minor, $this->currency_code)
        );
    }

    /**
     * Resolve the tag type namespace for the given user.
     */
    public static function tagTypeForUser(User $user): string
    {
        return 'income-user-'.$user->getKey();
    }

    /**
     * Sync tags for this income within the current user's tag type namespace.
     *
     * @param  array<int,string>  $tags
     */
    public function syncUserTags(array $tags): void
    {
        $this->syncTagsWithType($tags, self::tagTypeForUser($this->user));
    }
}
