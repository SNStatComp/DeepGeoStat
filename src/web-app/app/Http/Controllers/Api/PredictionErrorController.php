<?php

namespace App\Http\Controllers\Api;

use App\Events\DataCreationFailed;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PredictionErrorController extends Controller
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

        $predictionData = Cache::get("prediction.{$predictionKey}");
        if ($user = User::find($predictionData['user_id'])) {
            DataCreationFailed::dispatch($user, 'Something went wrong trying to create your predictions, please try again later.');
        }

        Cache::forget("prediction.{$predictionKey}");

        return response('Successfully informed user of fail.', 200);
    }
}
