<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\StorePredictions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PredictionFinishController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $predictionKey)
    {
        if (! Str::isUuid($predictionKey) || ! Cache::has("prediction.{$predictionKey}")) {
            abort(404);
        }

        $request->validate([
            'predictions' => ['required', 'array', 'min:1'],
        ]);

        $predictionData = Cache::get("prediction.{$predictionKey}");
        if (count($predictionData['grids']) !== count($request->predictions)) {
            abort(400);
        }

        StorePredictions::dispatch($predictionData, $request->predictions);

        Cache::forget("prediction.{$predictionKey}");

        return response('Successfully created predictions.', 200);
    }
}
