<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetGrid extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'source_dataset_grid_cell_id', 'polygon_id', 'source_dataset_grid_cell_change_id', 'polygon_change_id',
    ];

    public function sourceGridCell()
    {
        return $this->belongsTo(SourceDatasetGridCell::class, 'source_dataset_grid_cell_id', 'id');
    }

    public function sourceMask()
    {
        return $this->belongsTo(Mask::class, 'mask_id', 'id');
    }

    public function changeGridCell()
    {
        return $this->belongsTo(SourceDatasetGridCell::class, 'change_source_dataset_grid_cell_id', 'id');
    }

    public function changeMask()
    {
        return $this->belongsTo(Mask::class, 'change_mask_id', 'id');
    }
}
