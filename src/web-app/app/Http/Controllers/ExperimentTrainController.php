<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use Illuminate\Http\Request;

class ExperimentTrainController extends Controller
{
    public function start(Request $request, Experiment $experiment)
    {
        $experiment->start();

        return redirect()->route('experiments.show', $experiment)->with('flash', [
            'type' => 'info',
            'message' => 'Your experiment has started.',
        ]);
    }

    public function stop(Request $request, Experiment $experiment)
    {
        $experiment->stop();

        return redirect()->route('experiments.show', $experiment);
    }
}
