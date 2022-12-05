<?php

namespace App\Http\Controllers;

use App\Enums\TeamDetectionTypeEnum;
use App\Http\Resources\DatasetGridResource;
use App\Http\Resources\ExperimentDataResource;
use App\Models\ExperimentData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ExperimentDataController extends Controller
{
    public function show(Request $request, ExperimentData $experimentData)
    {
        $experimentData = $experimentData->load(['team', 'team.labelCLasses', 'team.defaultLabelClass'])->loadCount('labels');
        $paginateAmount = ($experimentData->team->detection_type === TeamDetectionTypeEnum::Classification) ? 9 : 10;

        // Apply Label Class filters.
        if ($request->labelClasses) {
            // TODO: Improve validation.
            $selectedLabelClassesIds = array_filter(Validator::make(explode(',', $request->labelClasses), [
                '*' => ['integer', 'exists:App\Models\LabelClass,id'],
            ])->validated());

            $selectedLabelClasses = $experimentData->team->labelClasses()->find($selectedLabelClassesIds)->pluck('id');
        }

        $labelDistribution = $experimentData->labelDistribution();

        $paginatedLabels = $experimentData->labels()->with(['sourceGridCell', 'sourceGridCell.dataset', 'sourceGridCell.gridCell', 'changeGridCell', 'changeGridCell.dataset', 'changeGridCell.gridCell', 'sourceMask', 'changeMask'])
                        ->orderBy('source_dataset_grid_cell_id', 'asc')
                        ->when(isset($selectedLabelClasses) && $selectedLabelClasses->isNotEmpty(), function ($query) use (&$selectedLabelClasses) {
                            $query->whereIn('label_class_id', $selectedLabelClasses);
                        })
                        ->paginate($paginateAmount)
                        ->withQueryString();

        $labels = collect($paginatedLabels->items())->map(function ($label) {
            return [
                'grid_id' => $label->id,
                'label_class_id' => $label->label_class_id,
            ];
        });

        return Inertia::render('Experiments/Data/Show', [
            'filters' => [
                'labelClasses' => (isset($selectedLabelClasses)) ? $selectedLabelClasses : $experimentData->team->labelClasses()->pluck('id'),
            ],
            'experimentData' => ExperimentDataResource::make($experimentData),
            'grids' => DatasetGridResource::collection($paginatedLabels),
            'labels' => $labels,
            'labelDistribution' => $labelDistribution,
        ]);
    }

    public function destroy(ExperimentData $experimentData)
    {
        $experimentData->delete();

        return redirect()->back();
    }
}
