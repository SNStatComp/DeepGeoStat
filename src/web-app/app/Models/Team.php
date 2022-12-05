<?php

namespace App\Models;

use App\Enums\TeamDetectionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'detection_type',
    ];

    protected $casts = [
        'detection_type' => TeamDetectionTypeEnum::class,
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function labelClasses()
    {
        return $this->hasMany(LabelClass::class);
    }

    public function defaultLabelClass()
    {
        return $this->hasOne(LabelClass::class, 'id', 'default_label_class_id');
    }

    public function datasets()
    {
        return $this->hasMany(Dataset::class);
    }

    public function labelEvidence()
    {
        return $this->hasMany(LabelEvidence::class);
    }

    public function experimentData()
    {
        return $this->hasMany(ExperimentData::class);
    }

    public function experiments()
    {
        return $this->hasMany(Experiment::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }
}
