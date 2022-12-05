<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExperimentData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description',
    ];

    public function labelDistribution()
    {
        $labelDistribution = [];
        $this->team->labelClasses->each(function ($labelClass) use (&$labelDistribution) {
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

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function labels()
    {
        return $this->hasMany(ExperimentDataLabel::class);
    }
}
