<?php

namespace App\Http\Controllers;

use App\Actions\DeepGeoStat\ConsolidateDataset;
use App\Enums\TeamDetectionTypeEnum;
use App\Http\Resources\DatasetGridResource;
use App\Http\Resources\DatasetResource;
use App\Models\Dataset;
use App\Models\InspectDataset;
use App\Rules\DatasetHasLabelEvidence;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InspectController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Inspect/Index', [
            'availableDatasets' => DatasetResource::collection(auth()->user()->currentTeam->datasets),
            'experimentData' => auth()->user()->currentTeam->experimentData,
            'labelClasses' => auth()->user()->currentTeam->labelClasses,
        ]);
    }

    public function show(Request $request, InspectDataset $inspectDataset)
    {
        $dataset = $inspectDataset->dataset;
        $paginateAmount = ($dataset->team->detection_type === TeamDetectionTypeEnum::Classification) ? 9 : 10;

        // Apply Label Class filters.
        if ($request->labelClasses) {
            // TODO: Improve validation.
            $selectedLabelClassesIds = array_filter(Validator::make(explode(',', $request->labelClasses), [
                '*' => ['integer', 'exists:App\Models\LabelClass,id'],
            ])->validated());

            $selectedLabelClasses = $dataset->team->labelClasses()->find($selectedLabelClassesIds)->pluck('id');
        }

        // Apply Confidence filters.
        // if ($request->confidence) {
        //     # TODO: Improve validation.
        //     $selectedConfidence = Validator::make(explode('-', $request->confidence), [
        //         '*' => ['integer', 'min:0', 'max:100'],
        //     ])->validated();
        // }

        // Count Label Distribution.
        $labelDistribution = $inspectDataset->labelDistribution();

        // Get filtered Labels with paginate.
        $paginatedLabels = $inspectDataset->labels()
                    ->orderBy('grid_id', 'asc')
                    ->when(isset($selectedLabelClasses) && $selectedLabelClasses->isNotEmpty(), function ($query) use (&$selectedLabelClasses) {
                        $query->whereIn('label_class_id', $selectedLabelClasses);
                    })
                    // ->when(isset($selectedConfidence), function ($query) use (&$selectedConfidence) {
                    //     $query->whereBetween('information->confidence->level', [0, 20]);
                    // })
                    ->paginate($paginateAmount)
                    ->withQueryString();

        // Take Grids associated with the Labels on the current page and make this a paginator.
        $grids = new LengthAwarePaginator(
            $dataset->grids()->with(['sourceGridCell', 'sourceGridCell.dataset', 'sourceGridCell.gridCell', 'changeGridCell', 'changeGridCell.dataset', 'changeGridCell.gridCell', 'sourceMask', 'changeMask'])->whereIn('id', collect($paginatedLabels->items())->pluck('grid_id'))->get(),
            $paginatedLabels->total(),
            $paginatedLabels->perPage(),
            $paginatedLabels->currentPage(),
            [
                'path' => $paginatedLabels->path(),
                'query' => Paginator::resolveQueryString(),
            ],
        );

        // Transform labels to non paginator to be able to display on the page.
        $labels = collect($paginatedLabels->items())->map(function ($label) {
            return [
                'grid_id' => $label['grid_id'],
                'label_class_id' => $label['label_class_id'],
                'information' => $label['information'],
            ];
        });

        return Inertia::render('Inspect/Show', [
            'filters' => [
                'labelClasses' => (isset($selectedLabelClasses)) ? $selectedLabelClasses : $dataset->team->labelClasses()->pluck('id'),
                'confidence' => (isset($selectedConfidence)) ? $selectedConfidence : [0, 100],
            ],
            'inspectDataset' => $inspectDataset,
            'dataset' => DatasetResource::make($dataset->load(['team', 'team.labelClasses', 'team.defaultLabelClass'])->loadCount('grids')),
            'grids' => DatasetGridResource::collection($grids),
            'labels' => $labels,
            'labelDistribution' => $labelDistribution,
        ]);
    }

    public function store(Request $request)
    {
        $request->validateWithBag('selectConsolidateDatasets', [
            'dataset' => ['required', 'integer', 'exists:App\Models\Dataset,id', new DatasetHasLabelEvidence],
        ]);

        $dataset = Dataset::findOrFail($request->dataset);

        // Dataset already has an inspect saved.
        if ($dataset->inspects()->exists()) {
            $latestInspect = $dataset->inspects()->latest()->first();

            // The most recent inspect for the dataset is more recent than the last updated label evidence.
            // This means there are no changes in the labels, so show the last inspect.
            if ($latestInspect->created_at > $dataset->labelEvidence()->orderBy('updated_at', 'desc')->first()->updated_at) {
                return redirect()->route('inspect.show', [
                    'inspectDataset' => $latestInspect,
                ]);
            }
        }

        $consolidateResult = ConsolidateDataset::execute($dataset);
        $inspectKey = (string) Str::orderedUuid();
        while (Cache::has("inspect.{$inspectKey}")) {
            $inspectKey = (string) Str::orderedUuid();
        }

        Cache::put("inspect.{$inspectKey}", [
            'id' => $inspectKey,
            'user_id' => auth()->id(),
            'dataset_id' => $dataset->id,
            'created_at' => Carbon::now(),
            ...$consolidateResult,
        ], now()->addDay());

        try {
            $httpRequest = Http::post(config('services.api.url').'/consolidate', [
                'id' => $inspectKey,
                ...$consolidateResult,
            ]);
        } catch (Exception $e) {
            $httpError = true;
        }

        if (! isset($httpError) && $httpRequest->successful()) {
            return redirect()->back()->with('flash', [
                'type' => 'info',
                'message' => 'Your selected dataset will be consolidated, you will be informed when this process is done.',
            ]);
        } else {
            Cache::forget("inspect.{$inspectKey}");

            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'Something went wrong trying to consolidate your dataset, please try again later.',
            ]);
        }
    }
}
