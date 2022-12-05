<?php

namespace App\Actions\DeepGeoStat;

use App\Enums\LabelEvidenceTypeEnum;
use App\Models\Dataset;

class ConsolidateDataset
{
    public static function execute(Dataset $dataset)
    {
        $team = $dataset->team;
        $registerEvidenceLabels = $dataset->labelEvidence()->where('type', LabelEvidenceTypeEnum::Register)->get()->pluck('labels')->map(function ($evidence) {
            return $evidence->map(function ($label) {
                return [
                    'grid_id' => $label['grid_id'],
                    'label_class_id' => $label['label_class_id'],
                ];
            });
        })->toArray();

        $otherEvidenceLabels = $dataset->labelEvidence()->where('type', '!=', LabelEvidenceTypeEnum::Register)->get()->pluck('labels')->map(function ($evidence) {
            return $evidence->map(function ($label) {
                return [
                    'grid_id' => $label['grid_id'],
                    'label_class_id' => $label['label_class_id'],
                ];
            });
        })->toArray();

        $consolidateData = [
            'information' => [
                'default_label_class' => $team->defaultLabelClass?->id,
                'label_classes' => $team->labelClasses()->pluck('id'),
                'grids' => $dataset->grids()->pluck('id'),
            ],
            'data' => [
                'registers' => $registerEvidenceLabels,
                'other' => $otherEvidenceLabels,
            ],
        ];

        return $consolidateData;
    }
}
