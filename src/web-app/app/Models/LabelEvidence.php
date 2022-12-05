<?php

namespace App\Models;

use App\Enums\LabelEvidenceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'dataset_id', 'title', 'description', 'type',
    ];

    protected $casts = [
        'type' => LabelEvidenceTypeEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function labels()
    {
        return $this->hasMany(LabelEvidenceLabel::class);
    }
}
