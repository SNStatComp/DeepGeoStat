<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function grids()
    {
        return $this->hasMany(DatasetGrid::class);
    }

    public function labelEvidence()
    {
        return $this->hasMany(LabelEvidence::class);
    }

    public function inspects()
    {
        return $this->hasMany(InspectDataset::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($dataset) {
            // Delete Label Evidence
            $dataset->labelEvidence()->each(function ($labelEvidence) {
                $labelEvidence->delete();
            });

            // Delete Inspect Dataset
            $dataset->inspects()->each(function ($inspect) {
                $inspect->delete();
            });

            // Delete Grids
            $dataset->grids()->delete();
        });
    }
}
