<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectDatasetLabel extends Model
{
    use HasFactory;

    protected $casts = [
        'information' => 'array',
    ];

    protected $fillable = [
        'grid_id', 'label_class_id', 'information',
    ];

    public function grid()
    {
        return $this->belongsTo(DatasetGrid::class);
    }
}
