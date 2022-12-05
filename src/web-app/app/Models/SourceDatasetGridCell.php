<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceDatasetGridCell extends Model
{
    use HasFactory;

    protected $table = 'source_dataset_grid_cell';

    public function dataset()
    {
        return $this->belongsTo(SourceDataset::class, 'source_dataset_id', 'id');
    }

    public function gridCell()
    {
        return $this->belongsTo(GridCell::class, 'grid_cell_id', 'id');
    }
}
