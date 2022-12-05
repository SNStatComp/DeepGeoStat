<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceDataset extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function gridCells()
    {
        return $this->belongsToMany(GridCell::class, 'source_dataset_grid_cell');
    }

    public function sourceGridCells()
    {
        return $this->hasMany(SourceDatasetGridCell::class, 'source_dataset_id', 'id');
    }
}
