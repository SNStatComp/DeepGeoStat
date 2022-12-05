<?php

namespace App\Http\Controllers;

use App\Enums\LabelEvidenceTypeEnum;
use App\Enums\TeamDetectionTypeEnum;
use App\Http\Requests\StoreLabelEvidenceRequest;
use App\Http\Resources\DatasetGridResource;
use App\Http\Resources\LabelEvidenceLabelResource;
use App\Http\Resources\LabelEvidenceResource;
use App\Jobs\CreateLabelEvidenceFromRegister;
use App\Models\LabelEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LabelEvidenceController extends Controller
{
    public function show(LabelEvidence $labelEvidence)
    {
        $paginateAmount = ($labelEvidence->team->detection_type === TeamDetectionTypeEnum::Classification) ? 9 : 10;
        $grids = $labelEvidence->dataset->grids()->orderBy('id', 'asc')->with(['sourceGridCell', 'sourceGridCell.dataset', 'sourceGridCell.gridCell', 'changeGridCell', 'changeGridCell.dataset', 'changeGridCell.gridCell', 'sourceMask', 'changeMask'])->paginate($paginateAmount);
        $labels = $labelEvidence->labels()->whereIn('grid_id', collect($grids->items())->pluck('id'))->get();

        return Inertia::render('Data/LabelEvidence/Show', [
            'can' => [
                'createLabels' => ($labelEvidence->type === LabelEvidenceTypeEnum::AnnotationCampaign) ? true : false,
            ],
            'labelEvidence' => LabelEvidenceResource::make($labelEvidence->load(['team', 'team.labelClasses', 'team.defaultLabelClass', 'dataset' => function ($query) {
                $query->withCount('grids');
            }])),
            'grids' => DatasetGridResource::collection($grids),
            'labels' => LabelEvidenceLabelResource::collection($labels),
        ]);
    }

    public function store(StoreLabelEvidenceRequest $request)
    {
        $type = LabelEvidenceTypeEnum::from($request->type);
        if ($type === LabelEvidenceTypeEnum::AnnotationCampaign) {
            DB::transaction(function () use ($request) {
                auth()->user()->currentTeam->labelEvidence()->create([
                    'user_id' => auth()->id(),
                    'dataset_id' => $request->dataset,
                    'title' => $request->title,
                    'description' => $request->description,
                    'type' => LabelEvidenceTypeEnum::AnnotationCampaign,
                ]);
            });

            return redirect()->route('data.index');
        } else {
            $user = auth()->user();
            $team = $user->currentTeam;
            $input = $request->safe()->except(['register_file']);
            if ($request->hasFile('register_file')) {
                $csvPath = $request->register_file->store('registers');

                CreateLabelEvidenceFromRegister::dispatch($user, $team, $input, $csvPath);
            } else {
                CreateLabelEvidenceFromRegister::dispatch($user, $team, $input);
            }

            return redirect()->route('data.index')->with('flash', [
                'type' => 'info',
                'message' => 'Your label evidence will be created, you will be informed when this is done',
            ]);
        }
    }

    public function destroy(Request $request, LabelEvidence $labelEvidence)
    {
        $labelEvidence->delete();

        return redirect()->route('data.index');
    }
}
