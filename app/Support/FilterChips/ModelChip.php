<?php

namespace App\Support\FilterChips;

use App\Support\Concerns\ResolvesDisplayName;
use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic chip that resolves an Eloquent model by id and displays its name.
 *
 * The model may expose one of the following for display, in order of priority:
 * - provided resolver callback
 * - toChipLabel()
 * - chipLabel()
 * - name/title attribute
 * - model key as a fallback
 */
class ModelChip extends AbstractChip
{
    use ResolvesDisplayName;

    /**
     * The resolved model instance
     */
    protected ?Model $resolvedModel = null;

    /**
     * The name resolver callback
     */
    protected ?Closure $nameResolver = null;

    /**
     * Create a new ModelChip instance.
     *
     * @param  class-string<Model>  $modelClass  The class name of the model to resolve
     */
    public function __construct(protected string $modelClass, protected int|string|null $id)
    {
        // Set default keys to remove
        $this->removeKeys('param');

        // Set the default name resolver
        $this->nameResolver(function (Model $model) {
            // common attributes
            return match (true) {
                isset($model->name) => (string) $model->name,
                isset($model->title) => (string) $model->title,
                default => (string) $model->getKey(),
            };
        });
    }

    /**
     * Create a new ModelChip instance.
     *
     * @param  class-string<Model>  $modelClass  The class name of the model to resolve
     */
    public static function make(string $modelClass, int|string|null $id): self
    {
        return new self($modelClass, $id);
    }

    /**
     * Set a custom name resolver callback for the model.
     */
    public function nameResolver(Closure $nameResolver): self
    {
        $this->nameResolver = $nameResolver;

        return $this;
    }

    /**
     * Get the display value for the chip.
     */
    protected function proceedValue(): string
    {
        return $this->resolveName($this->resolveModel(), $this->nameResolver);
    }

    /**
     * Check if the chip has no associated model.
     */
    public function isEmpty(): bool
    {
        return $this->resolveModel() === null;
    }

    /**
     * Returns the default label when no custom label is set.
     */
    protected function initialLabel(): string
    {
        return 'Model';
    }

    /**
     * Resolve and cache the associated model instance.
     */
    private function resolveModel(): ?Model
    {
        if (! is_null($this->resolvedModel)) {
            return $this->resolvedModel;
        }

        if ($this->id === null) {
            return null;
        }

        if (! class_exists($this->modelClass)) {
            return null;
        }

        if (! is_subclass_of($this->modelClass, Model::class)) {
            return null;
        }

        /** @var class-string<Model> $class */
        $class = $this->modelClass;

        return $this->resolvedModel = $class::query()->find($this->id);
    }
}
