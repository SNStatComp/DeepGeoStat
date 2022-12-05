<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\StoreInspectDataset;
use App\Models\Dataset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InspectFinishController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $inspectKey)
    {
        if (! Str::isUuid($inspectKey) || ! Cache::has("inspect.{$inspectKey}")) {
            abort(404);
        }

        $request->validate([
            'labels' => ['required', 'array', 'min:1'],
        ]);

        $inspectData = Cache::get("inspect.{$inspectKey}");
        $dataset = Dataset::findOrFail($inspectData['dataset_id']);
        $user = User::find($inspectData['user_id']);
        $labels = $request->labels;

        StoreInspectDataset::dispatch($dataset, $user, $inspectData, $labels);

        return response('Successfully created Inspect Dataset.', 200);
    }
}
