<?php

namespace App\Models;

use App\Enums\GridCellTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GridCell extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => GridCellTypeEnum::class,
    ];

    /**
     * Get a new query builder for the model's table.
     * Manipulate in case we need to convert geometrical fields to text.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        return parent::newQuery()
            ->addSelect(['*', DB::raw('st_xmin(geometry) AS x_min, st_xmax(geometry) as x_max, st_ymin(geometry) as y_min, st_ymax(geometry) as y_max')]);
    }
}
