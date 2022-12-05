<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InspectDataset extends Model
{
    use HasFactory;

    protected $table = 'inspect_dataset';

    protected $fillable = [
        'dataset_id',
    ];

    public function labelDistribution()
    {
        $labelDistribution = [];
        $this->dataset->team->labelClasses->each(function ($labelClass) use (&$labelDistribution) {
            $labelDistribution[$labelClass->id] = 0;
        });
        $this->labels()
                    ->select(DB::raw('label_class_id, COUNT(*)'))
                    ->groupBy('label_class_id')
                    ->get()
                    ->each(function ($labelClass) use (&$labelDistribution) {
                        $labelDistribution[$labelClass['label_class_id']] = $labelClass['count'];
                    });

        return collect($labelDistribution);
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function labels()
    {
        return $this->hasMany(InspectDatasetLabel::class);
    }
}
