<?php

namespace App\Http\Controllers;

use App\Enums\InspectSampleMethodEnum;
use App\Models\InspectDataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class InspectSaveController extends Controller
{
    public function store(Request $request, InspectDataset $inspectDataset)
    {
        $request->validateWithBag('consolidateDatasets', [
            'title' => ['required'],
            'description' => ['nullable'],
            'sample_method' => ['nullable', new Enum(InspectSampleMethodEnum::class)],
            // TODO: Random sample size better validation.
            'random_sample_size' => ['required_if:sample_method,'.InspectSampleMethodEnum::RandomSample->value],
        ]);

        if (! is_null($request->sample_method)) {
            $sampleMethod = InspectSampleMethodEnum::from($request->sample_method);
            switch ($sampleMethod) {
                case InspectSampleMethodEnum::RandomSample:
                    $labelAmount = $inspectDataset->labels()->count();
                    $randomSampleAmount = $request->random_sample_size;
                    if (! is_numeric($randomSampleAmount)) {
                        $randomSampleAmount = round($labelAmount * (round(str_replace('%', '', $randomSampleAmount) / 1) / 100));
                    }

                    $experimentDataLabels = $inspectDataset->labels()->with('grid')->inRandomOrder()->take($randomSampleAmount)->get();
                    break;
                case InspectSampleMethodEnum::EqualClassSizeSample:
                    $lowestLabelAmount = $inspectDataset->labelDistribution()->filter()->min();
                    $lowestLabels = $inspectDataset->labelDistribution()->filter(function ($amount, $labelClassId) use ($lowestLabelAmount) {
                        return $amount === $lowestLabelAmount;
                    });

                    // Get labels from the labels with the lowest amount of labels.
                    $equalLabels = $inspectDataset->labels()->with('grid')->whereIn('label_class_id', $lowestLabels->keys())->get();

                    // Go trough the other labels and get a random sample of the lowest amount of labels.
                    $inspectDataset->dataset->team->labelClasses()->whereNotIn('id', $lowestLabels->keys())->get()->each(function ($labelClass) use ($inspectDataset, $lowestLabelAmount, &$equalLabels) {
                        $labels = $inspectDataset->labels()->with('grid')->where('label_class_id', $labelClass->id)->inRandomOrder()->take($lowestLabelAmount)->get();

                        $equalLabels = $equalLabels->concat($labels);
                    });

                    $experimentDataLabels = $equalLabels;
                    break;
                default:
                    abort(403);
            }
        } else {
            $experimentDataLabels = $inspectDataset->labels()->with('grid')->get();
        }

        $experimentData = DB::transaction(function () use ($request, $inspectDataset, $experimentDataLabels) {
            $experimentData = $inspectDataset->dataset->team->experimentData()->create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $labels = $experimentDataLabels->map(function ($label) use ($experimentData) {
                return [
                    'experiment_data_id' => $experimentData->id,
                    'source_dataset_grid_cell_id' => $label->grid->source_dataset_grid_cell_id,
                    'mask_id' => $label->grid->mask_id,
                    'change_source_dataset_grid_cell_id' => $label->grid->change_source_dataset_grid_cell_id,
                    'change_mask_id' => $label->grid->change_mask_id,
                    'label_class_id' => $label->label_class_id,
                ];
            })->toArray();

            foreach (array_chunk($labels, 1000) as $chunk) {
                $experimentData->labels()->insert($chunk);
            }

            return $experimentData;
        });

        return redirect()->route('inspect.index');
    }
}
