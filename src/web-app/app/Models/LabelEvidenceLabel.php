<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelEvidenceLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'grid_id', 'label_class_id', 'probability',
    ];

    protected $touches = ['labelEvidence'];

    public function user()
    {
    }

    public function labelEvidence()
    {
        return $this->belongsTo(LabelEvidence::class);
    }

    public function grid()
    {
        return $this->belongsTo(DatasetGrid::class);
    }

    public function labelClass()
    {
        return $this->belongsTo(LabelClass::class);
    }
}
