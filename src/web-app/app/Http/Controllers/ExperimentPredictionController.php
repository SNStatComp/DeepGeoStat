<?php

namespace App\Http\Controllers;

use App\Enums\TeamDetectionTypeEnum;
use App\Jobs\CreatePredictions;
use App\Models\Experiment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExperimentPredictionController extends Controller
{
    public function store(Request $request, Experiment $experiment)
    {
        $input = $request->validateWithBag('createPredictions', [
            'datasets' => ['required', 'array',
                Rule::when($experiment->team->detection_type === TeamDetectionTypeEnum::Classification, [
                    'min:1',
                ]),
                Rule::when($experiment->team->detection_type === TeamDetectionTypeEnum::ChangeDetection, [
                    'size:2',
                ]),
            ],
            'datasets.*' => ['integer', 'exists:App\Models\SourceDataset,id'],
            'title' => ['required'],
            'surface_usage_filters' => ['nullable', 'array'],
            'surface_usage_filters.*' => ['integer', 'exists:App\Models\SurfaceUsageFilter,id'],
            'surface_usage_filter_mask' => ['nullable', 'boolean'],
            'surface_usage_filter_mask_size' => [Rule::requiredIf(fn () => $request->surface_usage_filter_mask), 'nullable', 'regex:/\b(?<!\.)(?!0+(?:\.0+)?%)(?:\d|[1-9]\d|100)(?:(?<!100)\.\d+)?%/'],
        ]);

        CreatePredictions::dispatch(auth()->user(), $experiment, $input);

        return redirect()->back()->with('flash', [
            'type' => 'info',
            'message' => 'Your predictions are being processed, you will be informed when this process is done.',
        ]);
    }
}
