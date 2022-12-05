<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExperimentStatusEnum;
use App\Events\DataCreationSuccessful;
use App\Http\Controllers\Controller;
use App\Models\Experiment;
use Illuminate\Http\Request;

class ExperimentFinishController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Experiment $experiment)
    {
        $request->validate([
            'accuracy' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+((.)|(.\d{0,1})?)$/'],
            'precision' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+((.)|(.\d{0,1})?)$/'],
            'recall' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+((.)|(.\d{0,1})?)$/'],
            'f1_score' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+((.)|(.\d{0,1})?)$/'],
        ]);

        $experiment->update([
            'status' => ExperimentStatusEnum::Finished,
            'statistics' => [
                'accuracy' => $request->accuracy,
                'precision' => $request->precision,
                'recall' => $request->recall,
                'f1_score' => $request->f1_score,
            ],
        ]);

        if ($user = $experiment->user()->exists()) {
            DataCreationSuccessful::dispatch($user, "Successfully finished training your experiment {$experiment->title}");
        }

        return response('Successfully updated Experiment status to finished.', 200);
    }
}
