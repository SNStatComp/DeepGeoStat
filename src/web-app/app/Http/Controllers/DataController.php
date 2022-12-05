<?php

namespace App\Http\Controllers;

use App\Enums\TeamDetectionTypeEnum;
use App\Http\Resources\RegisterResource;
use App\Http\Resources\SurfaceUsageFilterResource;
use App\Http\Resources\TeamResource;
use App\Models\Region;
use App\Models\Register;
use App\Models\SourceDataset;
use App\Models\SurfaceUsageFilter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DataController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Data/Index', [
            'team' => TeamResource::make(auth()->user()->currentTeam()->with(['defaultLabelClass', 'labelClasses', 'datasets', 'labelEvidence'])->first()),
            'availableDatasets' => SourceDataset::orderByDesc('year')->get(),
            'availableFilters' => [
                'surfaceUsage' => SurfaceUsageFilterResource::collection(SurfaceUsageFilter::orderBy('code')->orderBy('year')->get()),
                'regions' => collect(Region::where('year', 2015)->select(['id', 'name', 'region_type'])->get()->toArray())->groupBy('region_type'),
            ],
            'availableRegisters' => RegisterResource::collection(
                Register::orderBy('source_type')->with('labelClasses')
                    ->when(auth()->user()->currentTeam->detection_type === TeamDetectionTypeEnum::Classification, function ($query) {
                        $query->whereNull('change_source_type');
                    })
                    ->when(auth()->user()->currentTeam->detection_type === TeamDetectionTypeEnum::ChangeDetection, function ($query) {
                        $query->whereNotNull('change_source_type');
                    })
                    ->get()),
        ]);
    }
}
