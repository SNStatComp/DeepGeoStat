<?php

namespace App\Http\Controllers;

use App\Enums\ExperimentModelTypeEnum;
use App\Enums\ExperimentStatusEnum;
use App\Http\Resources\ExperimentResource;
use App\Http\Resources\SurfaceUsageFilterResource;
use App\Models\Experiment;
use App\Models\SourceDataset;
use App\Models\SurfaceUsageFilter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class ExperimentController extends Controller
{
    public function index()
    {
        return Inertia::render('Experiments/Index', [
            'experiment_data' => auth()->user()->currentTeam->experimentData,
            'experiment_model_types' => array_column(ExperimentModelTypeEnum::cases(), 'value', 'name'),
            'experiments' => ExperimentResource::collection(auth()->user()->currentTeam->experiments()->orderBy('id', 'asc')->get()),
        ]);
    }

    public function store(Request $request)
    {
        $request->validateWithBag('createExperiment', [
            'train_data' => ['required', 'integer', 'exists:App\Models\ExperimentData,id'],
            'test_data' => ['nullable', 'integer', 'different:train_data', 'exists:App\Models\ExperimentData,id'],
            'title' => ['required'],
            'description' => ['nullable'],
            'model' => ['required', new Enum(ExperimentModelTypeEnum::class)],
            'epochs' => ['required', 'numeric', 'integer'],
            'batch_size' => ['required', 'numeric', 'integer'],
            'learning_rate' => ['required', 'numeric'],
            'early_stopping' => ['required', 'numeric'],
        ]);

        DB::beginTransaction();

        $experiment = auth()->user()->currentTeam->experiments()->create([
            'user_id' => auth()->id(),
            'train_experiment_data_id' => $request->train_data,
            'test_experiment_data_id' => $request->test_data,
            'title' => $request->title,
            'description' => $request->description,
            'status' => ExperimentStatusEnum::Idle,
            'options' => [
                'model' => $request->model,
                'epochs' => $request->epochs,
                'batch_size' => $request->batch_size,
                'learning_rate' => $request->learning_rate,
                'early_stopping' => $request->early_stopping,
            ],
        ]);

        try {
            $httpRequest = Http::post(config('services.api.url').'/experiments', $experiment->createExperimentData());
        } catch (Exception $e) {
            $httpError = true;
        }

        if (! isset($httpError) && $httpRequest->successful()) {
            DB::commit();

            return redirect()->route('experiments.show', $experiment);
        } else {
            DB::rollBack();

            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'Something went wrong trying to create your experiment, please try again later.',
            ]);
        }
    }

    public function show(Request $request, Experiment $experiment)
    {
        $experiment = $experiment->load(['predictions']);

        return Inertia::render('Experiments/Show', [
            'availableDatasets' => SourceDataset::orderByDesc('year')->get(),
            'availableFilters' => [
                'surfaceUsage' => SurfaceUsageFilterResource::collection(SurfaceUsageFilter::orderBy('code')->orderBy('year')->get()),
            ],
            'experiment' => $experiment,
            'learning_curve_detailed' => Http::get(config('services.api.url').'/experiments/'.$experiment->id.'/learning/detail')->json(),
            'confusion_matrix' => Http::get(config('services.api.url').'/experiments/'.$experiment->id.'/confusion')->json(),
        ]);
    }

    public function destroy(Request $request, Experiment $experiment)
    {
        $experiment->delete();

        return redirect()->route('experiments.index');
    }
}
