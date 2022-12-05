<?php

namespace App\Models;

use App\Enums\ExperimentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Experiment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'train_experiment_data_id', 'test_experiment_data_id', 'title', 'description', 'status', 'options', 'statistics',
    ];

    protected $casts = [
        'status' => ExperimentStatusEnum::class,
        'options' => 'array',
        'statistics' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function trainData()
    {
        return $this->hasOne(ExperimentData::class, 'id', 'train_experiment_data_id');
    }

    public function testData()
    {
        return $this->hasOne(ExperimentData::class, 'id', 'test_experiment_data_id');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }

    public function start()
    {
        if (Http::post(config('services.api.url').'/experiments/'.$this->id.'/start')->successful()) {
            $this->update([
                'status' => ExperimentStatusEnum::Training,
            ]);
        }
    }

    public function stop()
    {
        if (Http::post(config('services.api.url').'/experiments/'.$this->id.'/stop')->successful()) {
            $this->update([
                'status' => ExperimentStatusEnum::Stopped,
            ]);
        }
    }

    public function createExperimentData(): array
    {
        return [
            'information' => [
                'experiment' => [
                    'id' => $this->id,
                    'type' => $this->team->detection_type->value,
                ],
                'label_classes' => $this->team->labelClasses->map(function ($labelClass) {
                    return $labelClass->title;
                }),
            ],
            'options' => $this->options,
            'data' => [
                'train' => $this->createExperimentLabelData($this->trainData),
                'test' => ($this->testData()->exists()) ? $this->createExperimentLabelData($this->testData) : null,
            ],
        ];
    }

    private function createExperimentLabelData($experimentData): array
    {
        $labels = $experimentData->labels()->with(['sourceGridCell', 'sourceGridCell.dataset', 'sourceGridCell.gridCell', 'sourceMask', 'changeGridCell', 'changeGridCell.dataset', 'changeGridCell.gridCell', 'changeMask', 'labelClass'])->get();

        return $labels->map(function ($label) {
            return [
                'grids' => array_filter([
                    $this->createGridData($label->sourceGridCell, $label->sourceMask),
                    ($label->changeGridCell()->exists()) ? $this->createGridData($label->changeGridCell, $label->changeMask) : null,
                ]),
                'label' => $label->labelClass->title,
            ];
        })->toArray();
    }

    private function createGridData($grid, $mask): array
    {
        return array_filter([
            'dataset' => [
                'grid_cell_size' => $grid->gridCell->type->value,
                'image_type' => $grid->dataset->image_type,
                'year' => $grid->dataset->year,
            ],
            'grid_cell' => $grid->gridCell->title,
            'mask_index' => (! is_null($mask)) ? $mask->options['index'] : null,
        ]);
    }
}
