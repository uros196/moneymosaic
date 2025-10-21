<?php

namespace App\Traits;

use Illuminate\Validation\Validator;

/**
 * Trait ValidatorAwareTrait
 *
 * This trait provides validator awareness functionality by allowing
 * classes to store and manage a Validator instance.
 */
trait ValidatorAwareTrait
{
    protected Validator $validator;

    /**
     * Set the current validator.
     *
     * @return $this
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
