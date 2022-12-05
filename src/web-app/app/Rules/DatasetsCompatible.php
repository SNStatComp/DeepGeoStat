<?php

namespace App\Rules;

use App\Models\Dataset;
use Illuminate\Contracts\Validation\Rule;

class DatasetsCompatible implements Rule
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
        // Check if there are 2 or more datasets selected. If not there is no need for a compatability check.
        if (count($value) > 1) {
            $datasets = Dataset::find($value);

            // Check if datasets have same Grid Cell Type.
            $grid_cells_type = null;
            foreach ($datasets as $dataset) {
                $grid_cell_type = $dataset->gridCells()->first()->type;
                if ($grid_cells_type === null) {
                    $grid_cells_type = $grid_cell_type;
                }

                if ($grid_cells_type !== $grid_cell_type) {
                    return false;
                }
            }

            // Check if datasets have the same amount of Grid Cells.
            $grid_cells_count = null;
            foreach ($datasets as $dataset) {
                $grid_cell_count = $dataset->gridCells()->count();
                if ($grid_cells_count === null) {
                    $grid_cells_count = $grid_cell_count;
                }

                if ($grid_cells_count !== $grid_cell_count) {
                    return false;
                }
            }

            // TODO: In the future might add compatibility check for actual Grid Cells instead of just count.
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be compatible.';
    }
}
