<?php

namespace App\Rules;

use App\Models\Dataset;
use Illuminate\Contracts\Validation\Rule;

class DatasetHasLabelEvidence implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $dataset = Dataset::findOrFail($value);

        // Dataset has Label Evidence
        if ($dataset->labelEvidence->isNotEmpty()) {
            if ($dataset->team->defaultLabelClass()->exists()) {
                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must have label evidence associated to it.';
    }
}
