<?php

namespace App\Http\Controllers;

use App\Enums\TeamDetectionTypeEnum;
use App\Http\Requests\StoreDatasetRequest;
use App\Http\Resources\DatasetGridResource;
use App\Http\Resources\DatasetResource;
use App\Jobs\CreateDataset;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DatasetController extends Controller
{
    public function show(Dataset $dataset)
    {
        $paginateAmount = ($dataset->team->detection_type === TeamDetectionTypeEnum::Classification) ? 9 : 10;

        return Inertia::render('Data/Datasets/Show', [
            'dataset' => DatasetResource::make($dataset->load(['team'])->loadCount(['grids'])),
            'grid' => DatasetGridResource::collection($dataset->grids()->orderBy('id', 'asc')->with(['sourceGridCell', 'sourceGridCell.dataset', 'sourceGridCell.gridCell', 'changeGridCell', 'changeGridCell.dataset', 'changeGridCell.gridCell', 'sourceMask', 'changeMask'])->paginate($paginateAmount)),
        ]);
    }

    public function store(StoreDatasetRequest $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam;
        $input = $request->validated();

        CreateDataset::dispatch($user, $team, $input);

        return redirect()->route('data.index')->with('flash', [
            'type' => 'info',
            'message' => 'Your dataset will be created, you will be informed when this is done',
        ]);
    }

    public function destroy(Request $request, Dataset $dataset)
    {
        $dataset->delete();

        return back();
    }
}
