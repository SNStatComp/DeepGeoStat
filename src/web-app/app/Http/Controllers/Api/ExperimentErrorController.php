<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExperimentStatusEnum;
use App\Events\DataCreationFailed;
use App\Http\Controllers\Controller;
use App\Models\Experiment;
use Illuminate\Http\Request;

class ExperimentErrorController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Experiment $experiment)
    {
        $experiment->update([
            'status' => ExperimentStatusEnum::Error,
        ]);

        DataCreationFailed::dispatch($experiment->user, "Something went wrong trying train your experiment {$experiment->title}, please try again later.");

        return response('Successfully informed user of fail.', 200);
    }
}
